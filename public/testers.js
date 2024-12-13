const jsonString = '{"firstName": "Joshua Rafael", "lastName": "Apilado", "age": 21, "country": "Philippines", "province": "La Union", "city": "San Fernando City"}';

// Parse JSON string into JavaScript object
const parsedData = JSON.parse(jsonString);

console.log(parsedData.firstName); // Output: Joshua Rafael 
console.log(parsedData.lastName);  // Output: Apilado
console.log(parsedData.age);       // Output: 21
console.log(parsedData.country);   // Output: Philippines
console.log(parsedData.province);  // Output: La Union
console.log(parsedData.city);      // Output: San Fernando City
