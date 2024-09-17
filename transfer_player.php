<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permintaan Transfer</title>
    <style>
   
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
            color: #333;
        }

 
        .form-container {
            max-width: 600px;
            margin: 40px auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }


        h1 {
            text-align: center;
            color: black;
            margin-bottom: 20px;
            font-size: 24px;
        }


        label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
            color: #333;
        }

      
        input[type="text"], input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

      
        input[type="text"]:focus {
            border-color: #007bff;
            outline: none;
        }

        input[type="submit"] {
            background-color: #48CFCB;
            color: #fff;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }

        
        input[type="submit"]:hover {
            background-color: #0056b3;
        }

  
        .warning-message {
            text-align: center;
            color: #d9534f;
            font-weight: bold;
            background-color: #f2dede;
            padding: 15px;
            border-radius: 4px;
            border: 1px solid #ebccd1;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Permintaan Transfer</h1>

        <?php
        session_start();
        include '../database/connect.php';

    
        if (!isset($_SESSION['username'])) {
            die("Anda harus login terlebih dahulu.");
        }

        $username = $_SESSION['username']; 

     
        $stmt = $mysqli->prepare("SELECT captain FROM account WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($is_captain);
        $stmt->fetch();
        $stmt->close();

        if ($is_captain) { 
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
              
                $player_name = isset($_POST['player_name']) ? $_POST['player_name'] : '';
                $from_club = isset($_POST['from_club']) ? $_POST['from_club'] : '';
                $to_club = isset($_POST['to_club']) ? $_POST['to_club'] : '';

                if (!empty($player_name) && !empty($from_club) && !empty($to_club)) {
                  
                    $stmt = $mysqli->prepare("INSERT INTO transfers (player_name, from_club, to_club) VALUES (?, ?, ?)");
                    $stmt->bind_param("sss", $player_name, $from_club, $to_club);

                    if ($stmt->execute()) {
                        echo "<p>Permintaan transfer berhasil dikirim!</p>";
                    } else {
                        echo "<p>Terjadi kesalahan: " . $mysqli->error . "</p>";
                    }

                    $stmt->close();
                } else {
                    echo "<p>Semua kolom harus diisi.</p>";
                }
            }
        ?>
            <form method="POST" action="">
                <label>Nama Pemain:</label>
                <input type="text" name="player_name" required>
                
                <label>Klub Asal:</label>
                <input type="text" name="from_club" required>
                
                <label>Klub Tujuan:</label>
                <input type="text" name="to_club" required>
                
                <input type="submit" value="Kirim Permintaan Transfer">
            </form>

        <?php
        } else {
       
            echo '<div class="warning-message">Hanya captain yang dapat mengakses halaman ini.</div>';
        }
        ?>
    </div>
</body>
</html>
