<?php
include '../database/connect.php';

session_start();
if (!isset($_SESSION['username'])) {
    die("Anda harus login terlebih dahulu.");
}
$username = $_SESSION['username'];


$stmt = $mysqli->prepare("SELECT admin FROM account WHERE username = ?");
if (!$stmt) {
    die('Prepare failed: ' . $mysqli->error);
}
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($is_admin);
$stmt->fetch();
$stmt->close();

if ($is_admin) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $transfer_id = $_POST['transfer_id'];
        $status = $_POST['status'];

      
        error_log("Transfer ID: $transfer_id");
        error_log("Status: $status");

      
        $stmt = $mysqli->prepare("UPDATE transfers SET status = ? WHERE id = ?");
        if (!$stmt) {
            die('Prepare failed: ' . $mysqli->error);
        }
        $stmt->bind_param("si", $status, $transfer_id);

        if ($stmt->execute()) {
      
            $stmt = $mysqli->prepare("SELECT player_name, from_club, to_club FROM transfers WHERE id = ?");
            if (!$stmt) {
                die('Prepare failed: ' . $mysqli->error);
            }
            $stmt->bind_param("i", $transfer_id);
            $stmt->execute();
            $stmt->bind_result($player_name, $from_club, $to_club);
            $stmt->fetch();
            $stmt->close();

            $announcement_message = "";
            if ($status == "approved") {
                $announcement_message = "Transfer: $player_name, from $from_club to $to_club, Done deal.";
            } else {
                $announcement_message = "Transfer rumor failed: $player_name, from $from_club to $to_club.";
            }

     
            $stmt = $mysqli->prepare("INSERT INTO announcements (message, status, created_at) VALUES (?, ?, NOW())");
            if (!$stmt) {
                die('Prepare failed: ' . $mysqli->error);
            }
            $stmt->bind_param("ss", $announcement_message, $status);

            if ($stmt->execute()) {
                $feedback_message = '<div class="alert success">Pengumuman berhasil dibuat.</div>';
            } else {
                $feedback_message = '<div class="alert error">Terjadi kesalahan: ' . $mysqli->error . '</div>';
            }
            $stmt->close();
        } else {
            $feedback_message = '<div class="alert error">Terjadi kesalahan: ' . $mysqli->error . '</div>';
        }
    }

 
    $stmt = $mysqli->prepare("SELECT id, player_name, from_club, to_club, status FROM transfers WHERE status = 'pending'");
    if (!$stmt) {
        die('Prepare failed: ' . $mysqli->error);
    }
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $feedback_message = '<div class="alert error">Hanya admin yang dapat mengakses halaman ini.</div>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transfer Requests</title>
    <style>
   
        body, h2, p, form, select, input {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

   
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            line-height: 1.6;
            padding: 20px;
        }

    
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

 
        h2 {
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
            color: #007bff;
            margin-bottom: 20px;
        }

    
        form {
            margin-top: 20px;
        }

        form div {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 15px;
            background-color: #fafafa;
        }

        form p {
            margin-bottom: 8px;
            font-size: 16px;
        }

        form input[type="hidden"] {
            display: none;
        }

        form select {
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ddd;
            margin-right: 10px;
        }

        form input[type="submit"] {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        form input[type="submit"]:hover {
            background-color: #0056b3;
        }

 
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            color: #fff;
            font-weight: bold;
        }

        .alert.success {
            background-color: #28a745;
        }

        .alert.error {
            background-color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php
        if (isset($feedback_message)) {
            echo $feedback_message;
        }
        
        if ($is_admin) {
            if ($result->num_rows > 0) {
                echo '<h2>Daftar Permintaan Transfer</h2>';
                echo '<form method="POST" action="">';
                while ($row = $result->fetch_assoc()) {
                    echo '<div>';
                    echo '<p>Player Name: ' . htmlspecialchars($row['player_name']) . '</p>';
                    echo '<p>From Club: ' . htmlspecialchars($row['from_club']) . '</p>';
                    echo '<p>To Club: ' . htmlspecialchars($row['to_club']) . '</p>';
                    echo '<p>Status: ' . htmlspecialchars($row['status']) . '</p>';
                    echo '<input type="hidden" name="transfer_id" value="' . htmlspecialchars($row['id']) . '">';
                    echo '<select name="status" required>';
                    echo '<option value="approved">Approve</option>';
                    echo '<option value="rejected">Reject</option>';
                    echo '</select>';
                    echo '<input type="submit" value="Update">';
                    echo '</div><hr>';
                }
                echo '</form>';
            } else {
                echo '<p>Tidak ada permintaan transfer yang menunggu persetujuan.</p>';
            }
        }
        ?>
    </div>
</body>
</html>
