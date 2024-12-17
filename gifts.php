<?php
require_once 'Database.php'; // Ielādē datubāzes pieslēguma klasi

// Izveido datubāzes savienojumu
$db = new Database();
$conn = $db->getConnection(); // Iegūstam savienojumu

// SQL vaicājums, lai iegūtu katru dāvanu, cik bērni vēlas šo dāvanu, un pieejamais skaits
$sql = "
    SELECT gifts.name AS gift_name, gifts.count_available,
           COUNT(DISTINCT children.id) AS gift_wishes
    FROM gifts
    LEFT JOIN letters ON letters.letter_text LIKE CONCAT('%', gifts.name, '%')
    LEFT JOIN children ON letters.sender_id = children.id
    LEFT JOIN grades ON children.id = grades.student_id
    WHERE (SELECT AVG(grades.grade) FROM grades WHERE grades.student_id = children.id) >= 5  -- Tikai bērni ar vidējo atzīmi >= 5
    GROUP BY gifts.id
";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>gifts.php</title>
    <link rel="stylesheet" href="style.css"> <!-- Pievieno CSS failu -->
    <style>
        /* CSS stilisti */
        .gift-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .gift-table th, .gift-table td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }

        .gift-table th {
            background-color: rgba(242, 242, 242, 0.9); /* 50% caurspīdīga pelēkā krāsa */
            position: sticky; /* Padara galveni lipīgu */
            top: 0; /* Paliek pie ekrāna augšas */
            z-index: 10; /* Nodrošina, ka galvene būs virs tabulas rindām */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Ēna galvenei */
        }

        .gift-table td {
            background-color: rgba(255, 255, 255, 0.8); /* 50% caurspīdīga balta krāsa */
        }

        .warning {
            color: red;
        }

        .info {
            color: blue;
        }

        .success {
            color: green;
        }
    </style>
</head>
<body>
    <div class="container">
        <table class="gift-table">
            <thead>
                <tr>
                    <th>Dāvanas Nosaukums</th>
                    <th>Pieejamais dāvanu skaits</th>
                    <th>Cik bērni vēlas šo dāvanu</th>
                    <th>Paziņojums</th>
                </tr>
            </thead>
            <tbody>

            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $gift_name = htmlspecialchars($row["gift_name"]);
                    $available_count = $row["count_available"];
                    $wished_count = $row["gift_wishes"];

                    // Nosaka paziņojumu par pieejamību
                    if ($wished_count > $available_count) {
                        $message = "Trūkst dāvanu!";
                        $message_class = "warning";
                    } elseif ($wished_count < $available_count) {
                        $message = "Ir par daudz dāvanu!";
                        $message_class = "info";
                    } else {
                        $message = "Dāvanu skaits ir pietiekošs.";
                        $message_class = "success";
                    }

                    // Izvada dāvanu informāciju tabulā
                    echo '
                    <tr>
                        <td>' . $gift_name . '</td>
                        <td>' . $available_count . '</td>
                        <td>' . $wished_count . '</td>
                        <td class="' . $message_class . '">' . $message . '</td>
                    </tr>';
                }
            } else {
                echo '<tr><td colspan="4">Nav atrastas dāvanas.</td></tr>';
            }

            // Aizver datubāzes savienojumu
            $conn->close();
            ?>

            </tbody>
        </table>
    </div>
</body>
</html>
