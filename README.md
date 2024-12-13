# Library Management System with JWT

A secure and efficient Library Management System utilizing JSON Web Tokens (JWT) for authentication. This system is built with PHP using the Slim Framework and provides endpoints for user registration, login, and managing books and authors.

## Table of Contents
- [Features](#features)
- [Setup](#setup)
- [SQL Database](#sql-database)
- [Technologies Used](#technologies-used)
- [API Endpoints](#api-endpoints)
  - [Authentication](#authentication)
  - [Authors Management](#authors-management)
  - [Books Management](#books-management)
  - [Combined Operations](#combined-operations)
- [Security Notes](#security-notes)

## Features
- User Registration and Authentication
- Token-Based Authentication with JWT
- CRUD Operations for:
  - Books
  - Authors
- Secure Database Operations

## Setup

1. Clone the repository:
   ```bash
   git clone [your-repository-url]
   cd [repository-name]
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

3. Configure the database in your project:
   ```php
   $servername = "localhost";
   $username = "root";
   $password = "";
   $dbname = "library";
   ```

4. Set up the database schema:
   ```sql
   CREATE TABLE users (
       userid INT AUTO_INCREMENT PRIMARY KEY,
       username VARCHAR(255) UNIQUE,
       password VARCHAR(255)
   );

   CREATE TABLE authors (
       authorid INT AUTO_INCREMENT PRIMARY KEY,
       name VARCHAR(255)
   );

   CREATE TABLE books (
       bookid INT AUTO_INCREMENT PRIMARY KEY,
       title VARCHAR(255),
       authorid INT,
       FOREIGN KEY (authorid) REFERENCES authors(authorid)
   );
   ```

5. Start your local PHP server:
   ```bash
   php -S localhost:8000
   ```

## Technologies Used
- Backend: PHP with Slim Framework
- Database: MySQL
- Authentication: JWT (JSON Web Tokens)
- Dependencies: Firebase JWT, PSR-7 Implementation

## API Endpoints

### Authentication

#### Register Users
- **Endpoint:** `POST /register`
- **Request:**
   ```json
   {
     "username": "User Name",
     "password": "password123"
   }
   ```

#### Authenticate Users
- **Endpoint:** `POST /auth`
- **Request:**
   ```json
   {
     "username": "User Name",
     "password": "password123"
   }
   ```
- **Response:**
   ```json
   {
     "token": "{{jwt-token}}"
   }
   ```

### Authors Management

#### Create Author
- **Endpoint:** `POST /author/create`
- **Request:**
   ```json
   {
     "name": "Author Name",
     "token": "{{jwt-token}}"
   }
   ```

#### Read Authors
- **Endpoint:** `GET /author/read`
- **Headers:** `Authorization: Bearer {{jwt-token}}`

#### Update Author
- **Endpoint:** `PUT /author/update`
- **Request:**
   ```json
   {
     "authorid": 1,
     "name": "Updated Author Name",
     "token": "{{jwt-token}}"
   }
   ```

#### Delete Author
- **Endpoint:** `DELETE /author/delete`
- **Request:**
   ```json
   {
     "authorid": 1,
     "token": "{{jwt-token}}"
   }
   ```

### Books Management

#### Create Book
- **Endpoint:** `POST /book/create`
- **Request:**
   ```json
   {
     "title": "Book Title",
     "authorid": 1,
     "token": "{{jwt-token}}"
   }
   ```

#### Read Books
- **Endpoint:** `GET /book/read`
- **Headers:** `Authorization: Bearer {{jwt-token}}`

#### Update Book
- **Endpoint:** `PUT /book/update`
- **Request:**
   ```json
   {
     "bookid": 1,
     "title": "Updated Book Title",
     "authorid": 1,
     "token": "{{jwt-token}}"
   }
   ```

#### Delete Book
- **Endpoint:** `DELETE /book/delete`
- **Request:**
   ```json
   {
     "bookid": 1,
     "token": "{{jwt-token}}"
   }
   ```

### Combined Operations

#### Get Authors with Books
- **Endpoint:** `GET /books`
- **Headers:** `Authorization: Bearer {{jwt-token}}`
- **Response:**
   ```json
   [
     {
       "author": "Author Name",
       "books": ["Book 1", "Book 2"]
     }
   ]
   ```
