<?php
require_once 'Database.php'; // Ielādē datubāzes pieslēguma klasi

// Izveido datubāzes savienojumu
$db = new Database();
$conn = $db->getConnection(); // Iegūstam savienojumu
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>children.php</title>
    <link rel="stylesheet" href="style.css"> <!-- Pievieno CSS failu -->
</head>
<body>
    <div class="container">
        <div class="cards">

        <?php
        // SQL vaicājums, lai iegūtu bērnu datus un vēstules tekstu
        $sql = "
        SELECT children.firstname, children.middlename, children.surname, children.age, 
               letters.letter_text, GROUP_CONCAT(DISTINCT gifts.name SEPARATOR ', ') AS wishes,
               AVG(grades.grade) AS avg_grade
        FROM children
        JOIN letters ON children.id = letters.sender_id
        LEFT JOIN gifts ON letters.letter_text LIKE CONCAT('%', gifts.name, '%')
        LEFT JOIN grades ON children.id = grades.student_id
        GROUP BY children.id, letters.letter_text
        ";
    
        $result = $conn->query($sql);
    
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Iegūst pilnu vēlmes sarakstu
                $wishes = htmlspecialchars($row["wishes"] ?? 'No wishes listed');
    
                // Ievieto dāvanu nosaukumus vēstules tekstā, izceļot tos
                $letter_text = htmlspecialchars($row["letter_text"]);
    
                // Vidējā atzīme
                $avg_grade = $row['avg_grade'];
    
                // Noapaļo atzīmi uz leju, lai noņemtu daļu aiz komata
                $avg_grade = floor($avg_grade);
    
                // Pārbauda, vai vidējā atzīme ir zem 5 ballēm
                $grade_class = ($avg_grade < 5) ? 'red' : 'green';
    
                // Ja ir dāvanas, aizvieto tās vēstules tekstā ar treknrakstu
                if (!empty($row["wishes"])) {
                    $gifts = explode(', ', $row["wishes"]); // Atdala dāvanu nosaukumus
    
                    // Katram dāvanu nosaukumam izveido aizstāšanu ar <b>
                    foreach ($gifts as $gift) {
                        // Piešķir dāvanām krāsu atbilstoši atzīmei
                        $gift_color = ($avg_grade < 5) ? 'red' : 'green';
                        
                        // Pārveido dāvanu tekstu, pievienojot krāsu
                        $pattern = '/\b' . preg_quote($gift, '/') . '\b/i';  // Pievieno robežas, lai pārliecinātos, ka mēs meklējam pilnus vārdus
                        $replacement = "<span class='$gift_color'><b>$gift</b></span>";
                        $letter_text = preg_replace($pattern, $replacement, $letter_text);
                    }
                }
    
                // Izvada bērna informāciju, vēstuli un dāvanu sarakstu
                echo '
                    <div class="card">
                        <div class="card-header">
                            <h2>' . htmlspecialchars($row["firstname"]) . ' ' 
                                . htmlspecialchars($row["middlename"]) . ' ' 
                                . htmlspecialchars($row["surname"]) . '</h2>
                            <p>Vecums: ' . htmlspecialchars($row["age"]) . '</p>
                            <p class="avg-grade">Vidējā atzīme: ' . $avg_grade . '</p>
                        </div>
                        <div class="card-body">
                            <p>' . nl2br($letter_text) . '</p>
                            <h4>Vēlas:</h4>
                            <p class="' . $grade_class . '">' . $wishes . '</p>
                        </div>
                    </div>
                ';
            }
        } else {
            echo '<p>No letters found.</p>';
        }
    
        // Aizver datubāzes savienojumu
        $conn->close();
        ?>

        </div>
    </div>
</body>
</html>
