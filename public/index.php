<?php
//liblaries dowloaded inside the vendor
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response; 
use Firebase\JWT\JWT;
use Firebase\JWT\Key;


require '../src/vendor/autoload.php';

$app = new \Slim\App;

//reg
$app->post('/register', function (Request $request, Response $response, 
array $args) {
    error_reporting(E_ALL);
    $data = json_decode($request->getBody());
    $uname =$data->username;
    $pass =$data->password;
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "library";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $check_sql = "SELECT COUNT(*) FROM users WHERE username = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->execute([$uname]);
        $userExists = $stmt->fetchColumn();
        
        //delete this if error
        $key = 'server_hack';
        $iat = time();
        $payload = [
            'iss' => 'http://library.org',
            'aus' => 'http://library.com',
            'exp' => $iat + 300,
        ];
        $jwt = JWT::encode($payload, $key, 'HS256');

        // Set the token as a secure HTTP-only cookie
        setcookie("authToken", $jwt, time() + 3600, "/", "", false, false);
        //


        if ($userExists > 0) {
            return $response->getBody()->write(json_encode(array("status" => "fail", "data" => array("title" => "Username already taken"))));
        }
        $sql = "INSERT INTO users (username, password)
        VALUES ('". $uname ."', '". hash('sha256', $pass) ."' ) ";
        $conn->exec($sql);
        $response->getBody()->write(
            json_encode(array(
                "status"=>"success","data"=>null, "Token" => $jwt)));
    } catch(PDOException $e) {
        json_encode(array("
        status"=>"fail","data"=>
        array("title"=>$e->getMessage())));
    }

    $conn = null;
    return $response;


});

//verify the user authorization
$app->post('/verify', function (Request $request, Response $response, array $args) {
    error_reporting(E_ALL);
    $data = json_decode($request->getBody());
    $uname = $data->username;
    $pass = $data->password;
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "library";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "SELECT * FROM users WHERE username=:username AND password=:password";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':username' => $uname,
            ':password' => hash('SHA256', $pass)
        ]);

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($data) === 1) {
            // Generate JWT token
            $key = 'server_hack';
            $iat = time();
            $payload = [
                'iss' => 'http://library.org', // Issuer
                'aud' => 'http://library.com', // Audience
                'exp' => $iat + 3600,          // Expiration time (1 hour)
                "data" => array(
                    "username" => $data[0]['username']
                )
            ];
            $jwt = JWT::encode($payload, $key, 'HS256');

            // Set the token as an HTTP-only, secure cookie
            setcookie("authToken", $jwt, time() + 3600, "/", "", false, false);

            $response->getBody()->write(json_encode(array(
                "status" => "success",
                "data" => array("username" => $data[0]['username']),
                "token" => $jwt
            )));
        } else {
            $response->getBody()->write(json_encode(array(
                "status" => "fail",
                "data" => array("title" => "Authentication Failed")
            )));
        }
        
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(array(
            "status" => "fail",
            "data" => array("title" => $e->getMessage())
        )));
    }

    $conn = null;
    return $response;
});



// C

function verifyTokenOnce($token) {
    $key = 'server_hack';
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "library";

    try {
        // Decode the JWT
        $decoded = JWT::decode($token, new Key($key, 'HS256'));

        // Connect to the database
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Check if the token has already been used
        $sql = "SELECT COUNT(*) FROM used_tokens WHERE token = :token";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['token' => $token]);
        $isUsed = $stmt->fetchColumn();

        if ($isUsed > 0) {
            return null; // Token already used
        }

        // Mark the token as used
        $sql = "INSERT INTO used_tokens (token) VALUES (:token)";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['token' => $token]);

        return $decoded;

    } catch (Exception $e) {
        return null; // Invalid or expired token
    }
}


//authorcreate2
$app->post('/create', function (Request $request, Response $response, array $args) {
    error_reporting(E_ALL);
    $data = json_decode($request->getBody());
    $author_name = $data->name;
    $token = $data->token;
    $decoded = verifyTokenOnce($token);

    if (!$decoded) {
        return $response->getBody()->write(json_encode(array("status" => "fail", "data" => array("title" => "Invalid token"))));
    }

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "library";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Insert the author into the database
        $sql = "INSERT INTO authors (name) VALUES (:name)";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['name' => $author_name]);

        // Get the last inserted author ID
        $authorId = $conn->lastInsertId();

        $key = 'server_hack';
        $iat = time();
        $payload = [
            'iss' => 'http://library.org',
            'aus' => 'http://library.com',
            'exp' => $iat + 300,
            "data" => array("username" => $decoded->data->username)
        ];
        $jwt = JWT::encode($payload, $key, 'HS256');

        // Set the token as a secure HTTP-only cookie
        setcookie("authToken", $jwt, time() + 3600, "/", "", false, false);

        // Return the authorId and token in the response
        $response->getBody()->write(json_encode(array(
            "status" => "success",
            "token" => $jwt,
            "authorId" => $authorId, // Include the authorId here
            "data" => null
        )));

    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(array("status" => "fail", "data" => array("title" => $e->getMessage()))));
    }

    $conn = null;
    return $response;
});


//author read
$app->get('/readauthor', function (Request $request, Response $response, array $args) {
    error_reporting(E_ALL);
    $token = $request->getHeader('Authorization')[0]; 
    $token = str_replace('Token ', '', $token);
    $decoded = verifyTokenOnce($token);
    if (!$decoded) {
        return $response->getBody()->write(json_encode(array("status" => "fail", "data" => array("title" => "Invalid token"))));
    }
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "library";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "SELECT * FROM authors";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $key = 'server_hack';
        $iat = time();
        $payload = [
            'iss' => 'http://library.org',
            'aus' => 'http://library.com',
            'exp' => $iat + 300,
            "data" => array("username" => $decoded->data->username)
        ];
        $jwt = JWT::encode($payload, $key, 'HS256');

        // Set the token as a secure HTTP-only cookie
        setcookie("authToken", $jwt, time() + 3600, "/", "", false, false);



        $authors = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode(array("status" => "success", "data" => $authors, "token" => $jwt)));

    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(array("status" => "fail", "data" => array("title" => $e->getMessage()))));
    }

    $conn = null;
    return $response;
});


//update
$app->put('/updateauthor', function (Request $request, Response $response, array $args) {
    error_reporting(E_ALL);
    $data = json_decode($request->getBody());
    $author_id = $data->authorid;
    $author_name = $data->name;
    $token = $request->getHeaderLine('Authorization');
    $token = str_replace('Token ', '', $token);

    $key = 'server_hack';
    
    try {
        // Decode JWT token
        $decoded = JWT::decode($token, new Key($key, 'HS256'));
    } catch (Exception $e) {
        return $response->getBody()->write(json_encode(array("status" => "fail", "data" => array("title" => "Invalid token: " . $e->getMessage()))));
    }

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "library";

    try {
        // Connect to the database
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Check if author exists
        $check_sql = "SELECT COUNT(*) FROM authors WHERE authorId = :authorId";
        $stmt = $conn->prepare($check_sql);
        $stmt->execute(['authorId' => $author_id]);
        $authorExists = $stmt->fetchColumn();
        
        if ($authorExists == 0) {
            return $response->getBody()->write(json_encode(array("status" => "fail", "data" => array("title" => "Author not found.")))); 
        }

        
        $check_name_sql = "SELECT COUNT(*) FROM authors WHERE name = :name AND authorId != :authorId";
        $stmt = $conn->prepare($check_name_sql);
        $stmt->execute(['name' => $author_name, 'authorId' => $author_id]);
        $nameExists = $stmt->fetchColumn();
        
        if ($nameExists > 0) {
            return $response->getBody()->write(json_encode(array("status" => "fail", "data" => array("title" => "This author name already exists.")))); 
        }

        // Update the author's name
        $update_sql = "UPDATE authors SET name = :name WHERE authorId = :authorId";
        $stmt = $conn->prepare($update_sql);
        $stmt->execute(['name' => $author_name, 'authorId' => $author_id]);

        // Check if the update was successful
        if ($stmt->rowCount() > 0) {
            // Generate new token
            $key = 'server_hack';
            $iat = time();
            $payload = [
                'iss' => 'http://library.org',
                'aud' => 'http://library.com',
                'exp' => $iat + 300,
                "data" => array(
                    "username" => $decoded->data->username
                )
            ];
            $new_token = JWT::encode($payload, $key, 'HS256');

            // Set the token as a secure HTTP-only cookie
            setcookie("authToken", $new_token, time() + 3600, "/", "", false, false);

            $response->getBody()->write(json_encode(array(
                "status" => "success",
                "token" => $new_token,
                "data" => null
            )));
        } else {
            return $response->getBody()->write(json_encode(array("status" => "fail", "data" => array("title" => "Failed to update author."))));
        }
        
        return $response->withHeader('Content-Type', 'application/json');

    } catch (PDOException $e) {
        return $response->getBody()->write(json_encode(array("status" => "fail", "data" => array("title" => "Database error: " . $e->getMessage()))));
    }

    $conn = null;
    return $response;
});


//author delete
$app->delete('/deleteauthor', function (Request $request, Response $response, array $args) {
    error_reporting(E_ALL);
    $data = json_decode($request->getBody());
    $author_id = $data->authorid;
    $token = $data->token; 
    $decoded = verifyTokenOnce($token);
    if (!$decoded) {
        return $response->getBody()->write(json_encode(array("status" => "fail", "data" => array("title" => "Invalid token"))));
    }
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "library";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "DELETE FROM authors WHERE authorid = :authorid";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['authorid' => $author_id]);
        $key = 'server_hack';
        $iat = time();
        $payload = [
            'iss' => 'http://library.org',
            'aus' => 'http://library.com',
            'exp' => $iat + 300,
            "data" => array("username" => $decoded->data->username)
        ];
        $jwt = JWT::encode($payload, $key, 'HS256');

        // Set the token as a secure HTTP-only cookie
        setcookie("authToken", $jwt, time() + 3600, "/", "", false, false);

        $response->getBody()->write(json_encode(array("status" => "success", "token" => $jwt, "data" => null)));

    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(array("status" => "fail", "data" => array("title" => $e->getMessage()))));
    }

    $conn = null;
    return $response;
});


// CRUD operations for books (token required)


//create book
$app->post('/registerbook', function (Request $request, Response $response, array $args) {
    error_reporting(E_ALL);
    $data = json_decode($request->getBody());
    $title = $data->title;
    $authorid = $data->authorid;
    $token = $data->token;
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "library";
    $key = 'server_hack';

    try {
        $decoded = JWT::decode($token, new Key($key, 'HS256'));
    } catch (Exception $e) {
        return $response->getBody()->write(json_encode(["status" => "fail", "message" => "Invalid Token"]));
    }

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $check_sql = "SELECT * FROM books WHERE title = ? AND authorid = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->execute([$title, $authorid]);
        $existing_book = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($existing_book) {
            return $response->getBody()->write(json_encode([
                "status" => "fail",
                "message" => "This book by the same author already exists."
            ]));
        }
        $sql = "INSERT INTO books (title, authorid) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$title, $authorid]);
        $bookid = $conn->lastInsertId();
        $sql = "INSERT INTO books_authors (bookid, authorid) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$bookid, $authorid]);
        $iat = time();
        $payload = [
            'iss' => 'http://library.org',
            'aud' => 'http://library.com',
            'exp' => $iat + 300,
            "data" => array(
                "username" => $decoded->data->username
            )
        ];
        $new_token = JWT::encode($payload, $key, 'HS256');

        // Corrected the variable name from $jwt to $new_token
        setcookie("authToken", $new_token, time() + 3600, "/", "", false, false);

        $response->getBody()->write(json_encode(array(
            "status" => "success",
            "token" => $new_token,
            "data" => null
        )));

    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(array("status" => "fail", "data" => array("title" => $e->getMessage()))));
    }

    $conn = null;
    return $response;
});



// read book
$app->get('/readbook', function (Request $request, Response $response, array $args) {
    error_reporting(E_ALL);
    $token = $request->getHeader('Authorization')[0];
    $token = str_replace('Token ', '', $token);
    $decoded = verifyTokenOnce($token);
    if (!$decoded) {
        return $response->getBody()->write(json_encode(array("status" => "fail", "data" => array("title" => "Invalid token"))));
    }
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "library";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "SELECT * FROM books";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $key = 'server_hack';
        $iat = time();
        $payload = [
            'iss' => 'http://library.org',
            'aud' => 'http://library.com',
            'exp' => $iat + 300,
            "data" => array(
                "username" => $decoded->data->username
            )
        ];
        $new_token = JWT::encode($payload, $key, 'HS256');

        // Corrected the variable name from $jwt to $new_token
        setcookie("authToken", $new_token, time() + 3600, "/", "", false, false);


        $response->getBody()->write(json_encode(array("status" => "success", "data" => $result, "token" => $new_token)));

    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(array("status" => "fail", "data" => array("title" => $e->getMessage()))));
    }

    $conn = null;
    return $response;
});

//update book
$app->put('/updatebook', function (Request $request, Response $response, array $args) {
    $data = json_decode($request->getBody());
    $bookid = $data->id ?? null;
    $title = $data->title ?? null;
    $authorid = $data->authorid ?? null;

    // Extract token from headers
    $headers = getallheaders();
    if (!isset($headers['Authorization'])) {
        return $response->withJson(["status" => "fail", "message" => "Authorization header missing"], 401);
    }
    $token = str_replace('Token ', '', $headers['Authorization']);

    // JWT Secret key
    $key = 'server_hack';

    // Verify and decode the token
    try {
        $decoded = JWT::decode($token, new Key($key, 'HS256'));
    } catch (\Firebase\JWT\ExpiredException $e) {
        return $response->withJson(["status" => "fail", "message" => "Token expired"], 401);
    } catch (\Exception $e) {
        return $response->withJson(["status" => "fail", "message" => "Invalid Token"], 401);
    }

    if (!$bookid || !$title || !$authorid) {
        return $response->withJson(["status" => "fail", "message" => "Missing required fields"], 400);
    }

    // Database credentials
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "library";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Check if the book with the same title and author already exists
        $check_sql = "SELECT * FROM books WHERE title = ? AND authorid = ? AND bookid != ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->execute([$title, $authorid, $bookid]);
        $existing_book = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing_book) {
            return $response->withJson([
                "status" => "fail",
                "message" => "This book by the same author already exists."
            ], 409);
        }

        // Check if the book exists before updating
        $book_check_sql = "SELECT * FROM books WHERE bookid = ?";
        $stmt = $conn->prepare($book_check_sql);
        $stmt->execute([$bookid]);
        $book_exists = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$book_exists) {
            return $response->withJson(["status" => "fail", "message" => "Book not found"], 404);
        }

        // Update the books table
        $sql = "UPDATE books SET title = ?, authorid = ? WHERE bookid = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$title, $authorid, $bookid]);

        // Update the books_authors table
        $sql = "UPDATE books_authors SET authorid = ? WHERE bookid = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$authorid, $bookid]);

        // Generate a new token
        $iat = time();
        $payload = [
            'iss' => 'http://library.org',
            'aud' => 'http://library.com',
            'exp' => $iat + 300,
            "data" => [
                "username" => $decoded->data->username
            ]
        ];
        $new_token = JWT::encode($payload, $key, 'HS256');

        // Set the new token in the response
        return $response->withJson([
            "status" => "success",
            "token" => $new_token,
            "data" => null
        ], 200);

    } catch (PDOException $e) {
        return $response->withJson([
            "status" => "fail",
            "message" => $e->getMessage()
        ], 500);
    }
});




//delete book
$app->delete('/deletebooks', function (Request $request, Response $response, array $args) {
    error_reporting(0);  // Disable error reporting to avoid unwanted output
    ob_start();  // Start output buffering

    $data = json_decode($request->getBody());
    $bookid = $data->bookid;
    $token = $data->token;
    $decoded = verifyTokenOnce($token);
    
    if (!$decoded) {
        $response->getBody()->write(json_encode(array("status" => "fail", "data" => array("title" => "Invalid token"))));
        ob_end_flush();
        return $response;
    }

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "library";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Delete from books_authors
        $sql = "DELETE FROM books_authors WHERE bookid = :bookid";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['bookid' => $bookid]);

        // Delete from books
        $sql = "DELETE FROM books WHERE bookid = :bookid";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['bookid' => $bookid]);

        // Generate new token after deletion
        $key = 'server_hack';
        $iat = time();
        $payload = [
            'iss' => 'http://library.org',
            'aus' => 'http://library.com',
            'exp' => $iat + 300,
            "data" => array("username" => $decoded->data->username)
        ];
        $new_token = JWT::encode($payload, $key, 'HS256');

        // Set the response content type to JSON
        $response = $response->withHeader('Content-Type', 'application/json');

        // Send the JSON response
        $response->getBody()->write(json_encode(array("status" => "success", "token" => $new_token, "data" => null)));

        // Set the new token in the cookie
        setcookie("authToken", $new_token, time() + 3600, "/", "", false, false);

    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(array("status" => "fail", "data" => array("title" => $e->getMessage()))));
    }

    $conn = null;
    ob_end_flush();  // Flush the buffered output after sending the response
    return $response;
});




//authors and its books
$app->get('/books', function (Request $request, Response $response, array $args) {
    error_reporting(E_ALL);
    
    // Retrieve token from header or cookie
    $token = $request->getHeader('Authorization')[0] ?? null;  // Check if token exists in header
    if (!$token) {
        $token = isset($_COOKIE['authToken']) ? $_COOKIE['authToken'] : null; // Fallback to cookie
    }
    
    // If token is not found
    if (!$token) {
        return $response->getBody()->write(json_encode(array("status" => "fail", "data" => array("title" => "Token not found. Please log in."))));
    }

    $token = str_replace('Token ', '', $token); // Remove 'Token ' prefix

    // Validate the token
    $decoded = verifyTokenOnce($token);
    if (!$decoded) {
        error_log("Invalid Token: " . $token);  // Log invalid token for debugging
        return $response->getBody()->write(json_encode(array("status" => "fail", "data" => array("title" => "Invalid token"))));
    }

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "library";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "SELECT authors.authorid, authors.name, GROUP_CONCAT(books.title) AS books
                FROM authors
                LEFT JOIN books ON books.authorid = authors.authorid
                GROUP BY authors.authorid, authors.name";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        $key = 'server_hack';
        $iat = time();
        $payload = [
            'iss' => 'http://library.org',
            'aud' => 'http://library.com',
            'exp' => $iat + 300,
            "data" => array(
                "username" => $decoded->data->username
            )
        ];
        $new_token = JWT::encode($payload, $key, 'HS256');

        // Corrected the variable name from $jwt to $new_token
        setcookie("authToken", $new_token, time() + 3600, "/", "", false, false);

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result = [];
        foreach ($data as $row) {
            $booksList = $row['books'] ? explode(",", $row['books']) : [];
            $result[] = [
                "author" => $row['name'],
                "books" => $booksList 
            ];
        }

        $response->getBody()->write(json_encode(["status" => "success", "data" => $result]));

    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(array("status" => "fail", "data" => array("title" => $e->getMessage()))));
    }

    $conn = null;
    return $response;
});

$app->run();





