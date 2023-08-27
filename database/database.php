<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
include '../connection/connection.php';

//--------------------- Create Contact Table---------------------//

$CreateTable = $database->prepare("CREATE TABLE Contact(id INT PRIMARY KEY NOT NULL AUTO_INCREMENT, username VARCHAR(120), email VARCHAR(120), company VARCHAR(120), message VARCHAR(255) )");
$CreateTable->execute();

//--------------------- Create Ticket Table---------------------//

$CreateTicketTable = $database->prepare("CREATE TABLE Ticket (
    id INT PRIMARY KEY AUTO_INCREMENT,
    TicketName VARCHAR(120),
    TicketEmail VARCHAR(120),
    TicketPhone VARCHAR(20), -- Adjusted length for phone number
    TicketForm VARCHAR(120),
    TicketFormTwo VARCHAR(120),
    TicketNumber VARCHAR(20), -- Adjusted length for ticket number
    TicketMessage TEXT -- Used TEXT type for longer message content
)");
$CreateTicketTable->execute();
?>