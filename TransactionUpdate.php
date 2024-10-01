<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Update Interface</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #4CAF50;
            color: white;
        }

        .form-container {
            margin-bottom: 20px;
        }

        input[type="text"],
        input[type="number"],
        input[type="password"] {
            padding: 10px;
            margin: 5px 0;
            width: 100%;
            box-sizing: border-box;
        }

        button {
            background-color: #5cb85c;
            color: white;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background-color: #4cae4c;
        }

        .cancel-button {
            background-color: #bb2b21;
            color: white;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
            display: none;
            margin-top: 10px;
        }

        .cancel-button:hover {
            background-color: #d13d3d;
        }
    </style>
</head>

<body>
    <?php
    // Conectar a la base de datos
    $conn = new mysqli("localhost", "root", "123456", "insertdpassword");

    // Verificar conexión
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

    // Verificar si los datos han sido enviados para guardado
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Name']) && isset($_POST['Total'])) {
        $Name = $_POST['Name'];
        $Total = $_POST['Total'];

        // Iniciar transacción
        $conn->begin_transaction();

        try {
            // Insertar datos en la tabla "transaccion"
            $sqlTransaccion = "INSERT INTO transaccion (Name, Total) VALUES ('$Name', '$Total')";
            if ($conn->query($sqlTransaccion) === FALSE) {
                throw new Exception("Error al insertar en 'transaccion': " . $conn->error);
            }

            // Confirmar transacción
            $conn->commit();
            echo "Registro guardado exitosamente.";
        } catch (Exception $e) {
            // Revertir transacción en caso de error
            $conn->rollback();
            echo "Error: " . $e->getMessage();
        }
    }

    // Obtener todos los datos de la tabla "transaccion"
    $sqlSelect = "SELECT Name, Total FROM transaccion";
    $result = $conn->query($sqlSelect);
    ?>

    <div class="form-container">
        <h2>Insert Transaction</h2>
        <form id="transactionForm" method="POST" action="">
            <input type="text" id="Name" name="Name" placeholder="Recipient's Name" required>
            <input type="number" id="Total" name="Total" placeholder="Transaction Amount" required>
            <button type="button" onclick="askForPassword()">Insert</button>
        </form>
    </div>

    <table id="transactionTable">
        <thead>
            <tr>
                <th>Recipient's Name</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Verificar si hay resultados y mostrarlos
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr><td>" . $row["Name"] . "</td><td>" . $row["Total"] . "</td></tr>";
                }
            } else {
                echo "<tr><td colspan='2'>No records found</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <div id="passwordPrompt" style="display:none;">
        <h3>Please enter your password to confirm the Insert</h3>
        <input type="password" id="password" placeholder="Enter password">
        <button onclick="confirmTransaction()">Confirm</button>
        <button id="cancelButton" class="cancel-button" onclick="cancelTransaction()">Cancel</button>
    </div>

    <script>
        let pendingRow;

        function askForPassword() {
            const name = document.getElementById('Name').value;
            const amount = document.getElementById('Total').value;

            if (!name || !amount) {
                alert('Please fill out all fields.');
                return;
            }

            // Store the pending row data
            pendingRow = {
                name: name,
                amount: amount
            };

            // Show the password prompt
            document.getElementById('passwordPrompt').style.display = 'block';
        }

        function confirmTransaction() {
            const password = document.getElementById('password').value;

            if (password !== 'cocacola') {
                alert('Incorrect password.');
                return;
            }

            // Submit the form
            document.getElementById('transactionForm').submit();

            // Optionally, add the new row to the table after submission
            const table = document.getElementById('transactionTable').getElementsByTagName('tbody')[0];
            const newRow = table.insertRow();
            newRow.insertCell(0).innerText = pendingRow.name;
            newRow.insertCell(1).innerText = pendingRow.amount;

            alert('Transaction updated successfully.');

            // Reset form and password prompt
            resetFormAndPrompt();
        }

        function cancelTransaction() {
            alert('Transaction cancelled.');

            // Reset form and password prompt
            resetFormAndPrompt();
        }

        function resetFormAndPrompt() {
            // Hide password prompt and reset input fields
            document.getElementById('passwordPrompt').style.display = 'none';
            document.getElementById('password').value = '';
            document.getElementById('Name').value = '';
            document.getElementById('Total').value = '';
            document.getElementById('cancelButton').style.display = 'none';
        }
    </script>

</body>

</html>
