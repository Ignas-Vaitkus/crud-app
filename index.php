<?php
$hostname = 'localhost';
$username = 'crud_user';
$password = 'mysql';
$database = 'company';

try {
    $conn = mysqli_connect($hostname, $username, $password, $database);
    if (!$conn) {
        exit('Connection failed: ' . mysqli_connect_error());
    }
} catch (Exception $e) {
    exit('Caught exception: ' . $e->getMessage());
}

$table = $_GET['table'];

if (!($table == ('Employees' || 'Projects'))) {
    $table = 'Employees';
}

if ($table === 'Employees') {
    $headers = ['ID', 'First Name', 'Last Name'];
} else {
    $headers = ['ID', 'Project Name'];
}

if (isset($_GET['action']) and $_GET['action'] == 'delete') {
    $sql = "DELETE FROM $table WHERE id = ?"; //$database.

    try {
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            exit();
        }
        $stmt->bind_param('i', $_GET['id']);
        $res = $stmt->execute();

        $stmt->close();
    } catch (Exception $e) {
        exit('Caught exception: ' . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang='lt'>

<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>CRUD app</title>
    <style>
        * {
            font-family: 'Trebuchet MS', Arial, Helvetica, sans-serif;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
        }

        nav {
            overflow: hidden;
            background-color: #333;
        }

        nav a {
            float: left;
            color: #f2f2f2;
            text-align: center;
            padding: 14px 16px;
            text-decoration: none;
            font-size: 17px;
        }

        nav a:hover {
            background-color: #ddd;
            color: black;
        }

        nav a.active {
            background-color: #04AA6D;
            color: white;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        table td,
        table th {
            border: 1px solid #ddd;
            padding: 8px;
        }

        table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        table tr:hover {
            background-color: #ddd;
        }

        table th {
            padding-top: 12px;
            padding-bottom: 12px;
            text-align: left;
            background-color: #4CAF50;
            color: white;
        }
    </style>
</head>

<body>
    <?php

    // print('<div>' . $headers . '<div/>');

    // exit(var_dump($headers));

    $sql = "SELECT * FROM $table";
    $result = mysqli_query($conn, $sql);

    print('<nav>
        <a class="' . ($table === 'Projects' ? 'active' : '') . '" href="?table=Projects">Projects</a>
        <a class="' . ($table === 'Employees' ? 'active' : '') . '" href="?table=Employees">Employees</a>
    </nav>');

    if (mysqli_num_rows($result) > 0) {
        print('<table>');
        print('<thead>');
        print('<tr>');

        foreach ($headers as $header) {
            print("<th>$header</th>");
        }

        print('<th>Actions</th></tr>');
        print('</thead>');
        print('<tbody>');
        while ($row = mysqli_fetch_assoc($result)) {
            print('<tr>');

            foreach ($row as $cell) {
                print('<td>' . $cell . '</td>');
            }

            print('<td>' . '<a href="?table=' . $table . '&action=delete&id='  . $row['id']
                . '"><button>DELETE</button></a>' . '</td>'
                . '</tr>');
        }
        print('</tbody>');
        print('</table>');
    } else {
        echo '<div>0 results<div/>';
    }
    mysqli_close($conn);
    ?>
</body>

</html>