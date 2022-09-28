<?php

$hostname = 'localhost';
$username = 'crud_user';
$password = 'mysql';
$database = 'company_crud_user';

try {
    $conn = mysqli_connect($hostname, $username, $password, $database);
    if (!$conn) {
        exit('Connection failed: ' . mysqli_connect_error());
    }
} catch (Exception $e) {
    exit('Caught exception: ' . $e->getMessage());
}


if (isset($_GET['table']))
    $table = $_GET['table'];


//If table is neither Employees nor Projects, set Projects
if (!isset($table) || !($table == 'Employees' || $table == 'Projects')) {
    $table = 'Projects';
}

if ($table === 'Employees') {
    $headers = ['ID', 'First Name', 'Last Name'];
} else {
    $headers = ['ID', 'Project Name', 'Employees Responsible'];
}

if (isset($_GET['action']) && ($_GET['action'] == 'delete')) {
    try {
        $item = substr($table, 0, -1);
        $sql = "DELETE FROM Project_Employee WHERE $item" . "_id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $_GET['id']);
        $stmt->execute();
        $stmt->close();

        $sql = "DELETE FROM $table WHERE id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $_GET['id']);
        $stmt->execute();
        $stmt->close();
    } catch (Exception $e) {
        exit('Caught exception: ' . $e->getMessage());
    }
}

//This boolean will store wether something was stored or not in the update

//Prioritizing Project and Employee addition with project assignment
//$Field_was_updated = (isset($_POST['Project']) && $_POST['Project'] == '');

if (isset($_POST['action']) && $_POST['action'] == 'update') {

    try {
        $item = substr($table, 0, -1);
        $sql = "UPDATE $table SET";


        if ($table == 'Projects') {
            $sql .= ' project_name ="' . $_POST['project_name'] . '" ';
        } elseif ($table == 'Employees') {
            $sql .= ' firstname = "' . $_POST['firstname']
                . '", lastname = "' . $_POST['lastname'] . '" ';
        }

        $sql .= 'WHERE id = ?;';

        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $_POST['id']);
        $stmt->execute();
        $stmt->close();
    } catch (Exception $e) {
        exit('Caught exception: ' . $e->getMessage());
    }
}

if (isset($_POST['action']) && $_POST['action'] == 'insert') {
    try {
        if ($table == 'Projects') {

            $sql = "INSERT INTO $table (project_name) VALUES (?)";
            $stmt = $conn->prepare($sql);
            $project_name = ucfirst($_POST['project_name']);
            $stmt->bind_param('s', $project_name);
        } elseif ($table == 'Employees') {
            $sql = "INSERT INTO $table (firstname, lastname) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $firstname = ucfirst($_POST['firstname']);
            $lastname = ucfirst($_POST['lastname']);
            $stmt->bind_param('ss', $firstname, $lastname);
        }
        $stmt->execute();
        $stmt->close();
    } catch (Exception $e) {
        exit('Caught exception: ' . $e->getMessage());
    }
}

//Convert assign id to integer and validate if a valid number was entered

function convert_id_to_int($id)
{
    if (isset($id)) {
        $id = (int) $id;
        if ($id <= 0)
            exit('assignID or assignTO must be higher than 0 or is not a number');
        return $id;
    }
}


if (isset($_GET['assignID']))
    $_GET['assignID'] = convert_id_to_int($_GET['assignID']);
if (isset($_GET['assignTO']))
    $_GET['assignTO'] = convert_id_to_int($_GET['assignTO']);

//Assign new entry to Project_Employee table

if (isset($_GET['assignID']) && isset($_GET['assignTO'])) {
    try {
        $sql = "INSERT INTO Project_Employee VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        //Columns (project_id, employee_id)
        $stmt->bind_param('ii', $_GET['assignTO'], $_GET['assignID']);
        $stmt->execute();
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

<body id="table" class="<?php echo $table ?>">
    <?php

    print('<nav>
        <a class="' . ($table === 'Projects' ? 'active' : '')
        . '" href="?table=Projects">Projects</a>
        <a class="' . ($table === 'Employees' ? 'active' : '')
        . '" href="?table=Employees">Employees</a>
    </nav>');

    //A better practice would be to modularize this code for rendering the HTML
    //into different files. (And not have an 100 line long if statement)

    if ((!isset($_GET['assignID']) && !isset($_GET['assignTO'])) || (isset($_GET['assignID']) && isset($_GET['assignTO']))) {

        $sql = "SELECT
        Projects.id, 
        Projects.project_name,  #GROUP_CONCAT(DISTINCT Projects.project_name),
        Employees.firstname,    #GROUP_CONCAT(DISTINCT Employees.firstname),
        Employees.lastname      #GROUP_CONCAT(DISTINCT Employees.lastname)
        FROM Projects           #GROUP_CONCAT does not work on Joined tables
        LEFT JOIN Project_Employee
        ON Projects.id = Project_Employee.project_id
        LEFT JOIN Employees
        ON Employees.id = Project_Employee.employee_id;
        SELECT * FROM Employees;";

        $result = mysqli_multi_query($conn, $sql);

        $projects = mysqli_store_result($conn);
        mysqli_next_result($conn);
        $employees = mysqli_store_result($conn);

        if ($table === 'Projects') {
            $result = $projects;
        } else {
            $result = $employees;
        }

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
                $rows[] = $row;
            }

            $i = 0;

            while ($i < count($rows)) {

                print('<tr id="' . $i .  '">');


                print('<td>' . $rows[$i]['id'] . '</td>');

                if ($table === 'Projects') {
                    print('<td>' . $rows[$i]['project_name'] . '</td>');
                    print('<td>');
                    $temp = $rows[$i]['id'];

                    //Print employees in one cell
                    $different_project = false;
                    do {
                        print('<div>' . $rows[$i]['firstname'] . ' ' . $rows[$i]['lastname'] . '</div>');
                        $temp = $rows[$i]['id'];

                        (isset($rows[$i + 1]['id'])
                            && ($rows[$i]['id'] === $rows[$i + 1]['id']))
                            ? $i++
                            : $different_project = true;
                    } while (!$different_project);

                    print('</td>');
                } elseif ($table === 'Employees') {
                    print('<td>' . $rows[$i]['firstname'] . '</td>');
                    print('<td>' . $rows[$i]['lastname'] . '</td>');
                }

                print('<td><div><button class="update ' . $rows[$i]['id'] . '">UPDATE</button>' . '<a href="?table=' . $table . '&action=delete&id='  . $rows[$i]['id']
                    . '"><button>DELETE</button></a>'
                    . ($table == 'Employees'
                        ? ('<a href="?assignID=' . $rows[$i]['id'] . '"><button>ASSIGN PROJECT</button></a>') : '')
                    . '</div></td></tr>');

                $i++;
            }
            print('</tbody>');
            print('</table>');
        } else {
            echo '<div>0 results</div>';
        }

        print('<form action="./?table=' . $table . '" method="POST">');
        print('<input type="text" name="action" value="insert" style="display: none;">');
        if ($table == 'Projects') {
            print('<div>Please type a project name to add an entry, note that project name cannot be blank!</div>');
            print('<input type="text" name="project_name" placeholder="Project Name">');
        } elseif ($table == 'Employees') {
            print('<div>Please type an employee name to add an entry, note that the names cannot be blank!</div>');
            print('<input type="text" name="firstname" placeholder="First Name">');
            print('<input type="text" name="lastname" placeholder="Last Name">');
        }
        print('<button type="submit">ADD</button>');
        print('</form>');
    } elseif (isset($_GET['assignID']) && gettype($_GET['assignID']) == 'integer') {

        //This query selects projects that an employee is not assigned to

        $sql = 'SELECT Projects.id,
        Projects.project_name
        FROM Projects
        WHERE id NOT IN (
            SELECT Projects.id
            FROM Projects
            LEFT JOIN Project_Employee
            ON Projects.id = Project_Employee.project_id
            WHERE (Project_Employee.employee_id = '
            . $_GET['assignID'] . '));';

        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            print('<table>');
            print('<thead>');
            print('<tr>');
            print("<th>Id</th>");
            print("<th>Project Name</th>");
            print('<th>Actions</th></tr>');
            print('</thead>');
            print('<tbody>');



            while ($row = mysqli_fetch_assoc($result)) {
                print('<tr>');
                print('<td>' . $row['id'] . '</td>');
                print('<td>' . $row['project_name'] . '</td>');
                print('<td><div><a href="?table=Employees&assignID=' . $_GET['assignID'] . '&assignTO=' . $row['id'] . '"><button>ASSIGN TO</button></a>');
                print('</tr>');
            }

            //Selected employee name on page could be showed with an additional select query

            print('</tbody>');
        } else {
            print('<div>No projects to assign to!</div>');
        }
    }
    mysqli_close($conn);
    ?>




    <script type="text/javascript">
        // This script renders the update form for entries
        // Note that there is no way to close the from after opening it (Delayed for future)
        // Another note there is no data validation built in the form

        const table = document.getElementById('table').className;

        console.log();

        const updateHandler = (e) => {
            const id = e.target.classList[1];

            console.log(e.target.classList[1])

            const cell = e.target.parentElement.parentElement;

            let html = `<form action="./?table=${table}" method="POST">
            <input type="text" style="display: none;" name="action" value="update">
            <input type="text" style="display: none;" name="id" value="${id}">`


            if (table == 'Projects') {
                const projectName = cell.previousSibling.previousSibling.innerHTML;
                html += `<input type="text" name="project_name" value="${projectName}">`

            } else {
                const firstName = cell.previousSibling.previousSibling.innerHTML;
                const lastName = cell.previousSibling.innerHTML;

                html += `<input type="text" name="firstname" value="${firstName}">
                <input type="text" name="lastname" value="${lastName}">`;
            }

            html += `
            <button type="submit" style="background-color: lightred;">CONFIRM</button>
            </form>`;

            cell.innerHTML += html;
        }

        const elements = document.getElementsByClassName("update");

        Array.from(elements).forEach(element => {
            element.addEventListener('click', updateHandler);
        });
    </script>
</body>

</html>