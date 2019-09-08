<?php
// Page headers
header("Content-type: application/json; charset=utf-8");

// Database initialization
include "config.php";
$mysql = new mysqli($db_host, $db_user, $db_pass, $db_schema, $db_port);
if($mysql->connect_error){
    echo json_encode([
        "error" => "Unable to connect to database",
        "code" => false
    ]);

    return;
}

// Validate url
$url = $_POST["url"];
if(!isset($url) || !filter_var($url, FILTER_VALIDATE_URL)){
    echo json_encode([
        "error" => "Missing or Invalid URL provided",
        "code" => false
    ]);

    return;
}
$lowerUrl = strtolower($url);

$stmt = $mysql->prepare("SELECT code FROM links WHERE LOWER(url)=?");
$stmt->bind_param("s", $lowerUrl);
$stmt->execute();
if(($result = $stmt->get_result()) !== false){
    $data = $result->fetch_assoc();
    if($data !== null){
        echo json_encode([
            "error" => false,
            "code" => $data["code"]
        ]);

        return;
    }
}
$stmt->free_result();

// Create a new, unique 6 digit code
$code = null;
do{
    $code = bin2hex(random_bytes(3));
    $stmt = $mysql->prepare("SELECT code FROM links WHERE code=?");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    if(($result = $stmt->get_result()) !== false && $result->fetch_assoc() !== null){
        $code = null;
    }
    $stmt->free_result();
}while($code === null);

// Store code in database with the associated url
$stmt = $mysql->prepare("INSERT INTO links(code, url) VALUES(?, ?)");
$stmt->bind_param("ss", $code, $url);
$stmt->execute();

// Return created code
echo json_encode([
    "error" => false,
    "code" => $code
]);