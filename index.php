```html
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vazifalar Boshqaruvi Tizimi</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 800px; margin: auto; background: white; padding: 20px; border-radius: 5px; }
        h1, h2 { text-align: center; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input, button { width: 100%; padding: 10px; margin-bottom: 10px; }
        button { background-color: #28a745; color: white; border: none; cursor: pointer; }
        button:hover { background-color: #218838; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <div class="container">
        <?php
        // Sessiyani boshlash
        session_start();
        
        // Ma'lumotlar bazasiga ulanish sozlamalari
        $servername = "localhost";
        $username = "root";
        $password = "striker2004";
        $dbname = "vazifalar_boshqaruvi";
        
        // MySQL bilan ulanish
        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            die("Ulanishda xato yuz berdi: " . $conn->connect_error);
        }
        
        // Ma'lumotlar bazasini yaratish
        $sql = "CREATE DATABASE IF NOT EXISTS vazifalar_boshqaruvi";
        $conn->query($sql);
        $conn->select_db($dbname);
        
        // Foydalanuvchilar jadvalini yaratish
        $sql = "CREATE TABLE IF NOT EXISTS foydalanuvchilar (
            id INT AUTO_INCREMENT PRIMARY KEY,
            foydalanuvchi_nomi VARCHAR(50) NOT NULL UNIQUE,
            parol VARCHAR(255) NOT NULL,
            admin TINYINT(1) DEFAULT 0
        )";
        $conn->query($sql);
        
        // Vazifalar jadvalini yaratish
        $sql = "CREATE TABLE IF NOT EXISTS vazifalar (
            id INT AUTO_INCREMENT PRIMARY KEY,
            foydalanuvchi_id INT,
            nomi VARCHAR(255) NOT NULL,
            tavsif TEXT,
            holat VARCHAR(50) DEFAULT 'Yangi',
            yaratilgan_sana TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (foydalanuvchi_id) REFERENCES foydalanuvchilar(id)
        )";
        $conn->query($sql);
        
        // Ro‘yxatdan o‘tish
        if (isset($_POST['royxatdan_otish'])) {
            $foydalanuvchi_nomi = $_POST['foydalanuvchi_nomi'];
            $parol = password_hash($_POST['parol'], PASSWORD_DEFAULT);
            $sql = "INSERT INTO foydalanuvchilar (foydalanuvchi_nomi, parol) VALUES ('$foydalanuvchi_nomi', '$parol')";
            if ($conn->query($sql) === TRUE) {
                echo "<p class='success'>Ro‘yxatdan o‘tish muvaffaqiyatli yakunlandi!</p>";
            } else {
                echo "<p class='error'>Xato: " . $conn->error . "</p>";
            }
        }
        
        // Tizimga kirish
        if (isset($_POST['kirish'])) {
            $foydalanuvchi_nomi = $_POST['foydalanuvchi_nomi'];
            $parol = $_POST['parol'];
            $sql = "SELECT * FROM foydalanuvchilar WHERE foydalanuvchi_nomi='$foydalanuvchi_nomi'";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                $foydalanuvchi = $result->fetch_assoc();
                if (password_verify($parol, $foydalanuvchi['parol'])) {
                    $_SESSION['foydalanuvchi_id'] = $foydalanuvchi['id'];
                    $_SESSION['admin'] = $foydalanuvchi['admin'];
                    echo "<p class='success'>Tizimga kirish muvaffaqiyatli!</p>";
                } else {
                    echo "<p class='error'>Parol noto‘g‘ri!</p>";
                }
            } else {
                echo "<p class='error'>Foydalanuvchi topilmadi!</p>";
            }
        }
        
        // Vazifa qo‘shish
        if (isset($_POST['vazifa_qoshish']) && isset($_SESSION['foydalanuvchi_id'])) {
            $nomi = $_POST['nomi'];
            $tavsif = $_POST['tavsif'];
            $foydalanuvchi_id = $_SESSION['foydalanuvchi_id'];
            $sql = "INSERT INTO vazifalar (foydalanuvchi_id, nomi, tavsif) VALUES ('$foydalanuvchi_id', '$nomi', '$tavsif')";
            if ($conn->query($sql) === TRUE) {
                echo "<p class='success'>Vazifa muvaffaqiyatli qo‘shildi!</p>";
            } else {
                echo "<p class='error'>Xato: " . $conn->error . "</p>";
            }
        }
        
        // Vazifa holatini yangilash
        if (isset($_POST['holatni_yangilash']) && isset($_SESSION['foydalanuvchi_id'])) {
            $vazifa_id = $_POST['vazifa_id'];
            $holat = $_POST['holat'];
            $sql = "UPDATE vazifalar SET holat='$holat' WHERE id='$vazifa_id' AND foydalanuvchi_id='{$_SESSION['foydalanuvchi_id']}'";
            if ($conn->query($sql) === TRUE) {
                echo "<p class='success'>Vazifa holati yangilandi!</p>";
            } else {
                echo "<p class='error'>Xato: " . $conn->error . "</p>";
            }
        }
        
        // Vazifani o‘chirish
        if (isset($_POST['vazifa_ochirish']) && isset($_SESSION['foydalanuvchi_id'])) {
            $vazifa_id = $_POST['vazifa_id'];
            $sql = "DELETE FROM vazifalar WHERE id='$vazifa_id' AND foydalanuvchi_id='{$_SESSION['foydalanuvchi_id']}'";
            if ($conn->query($sql) === TRUE) {
                echo "<p class='success'>Vazifa muvaffaqiyatli o‘chirildi!</p>";
            } else {
                echo "<p class='error'>Xato: " . $conn->error . "</p>";
            }
        }
        ?>
        
        <?php if (!isset($_SESSION['foydalanuvchi_id'])): ?>
            <h2>Ro‘yxatdan o‘tish</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="foydalanuvchi_nomi">Foydalanuvchi nomi:</label>
                    <input type="text" name="foydalanuvchi_nomi" required>
                </div>
                <div class="form-group">
                    <label for="parol">Parol:</label>
                    <input type="password" name="parol" required>
                </div>
                <button type="submit" name="royxatdan_otish">Ro‘yxatdan o‘tish</button>
            </form>
            
            <h2>Tizimga kirish</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="foydalanuvchi_nomi">Foydalanuvchi nomi:</label>
                    <input type="text" name="foydalanuvchi_nomi" required>
                </div>
                <div class="form-group">
                    <label for="parol">Parol:</label>
                    <input type="password" name="parol" required>
                </div>
                <button type="submit" name="kirish">Kirish</button>
            </form>
        <?php else: ?>
            <h1>Vazifalar Boshqaruvi Tizimi</h1>
            <form method="POST">
                <div class="form-group">
                    <label for="nomi">Vazifa nomi:</label>
                    <input type="text" name="nomi" required>
                </div>
                <div class="form-group">
                    <label for="tavsif">Tavsif:</label>
                    <input type="text" name="tavsif">
                </div>
                <button type="submit" name="vazifa_qoshish">Vazifa qo‘shish</button>
            </form>
            
            <h2>Vazifalar ro‘yxati</h2>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Nomi</th>
                    <th>Tavsif</th>
                    <th>Holati</th>
                    <th>Yaratilgan sana</th>
                    <th>Amallar</th>
                </tr>
                <?php
                $foydalanuvchi_id = $_SESSION['foydalanuvchi_id'];
                $sql = "SELECT * FROM vazifalar WHERE foydalanuvchi_id='$foydalanuvchi_id'";
                $result = $conn->query($sql);
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['id'] . "</td>";
                    echo "<td>" . $row['nomi'] . "</td>";
                    echo "<td>" . $row['tavsif'] . "</td>";
                    echo "<td>";
                    echo "<form method='POST' style='display:inline;'>";
                    echo "<input type='hidden' name='vazifa_id' value='" . $row['id'] . "'>";
                    echo "<select name='holat'>";
                    echo "<option value='Yangi'" . ($row['holat'] == 'Yangi' ? ' selected' : '') . ">Yangi</option>";
                    echo "<option value='Bajarilmoqda'" . ($row['holat'] == 'Bajarilmoqda' ? ' selected' : '') . ">Bajarilmoqda</option>";
                    echo "<option value='Bajarildi'" . ($row['holat'] == 'Bajarildi' ? ' selected' : '') . ">Bajarildi</option>";
                    echo "</select>";
                    echo "<button type='submit' name='holatni_yangilash'>Yangilash</button>";
                    echo "</form>";
                    echo "</td>";
                    echo "<td>" . $row['yaratilgan_sana'] . "</td>";
                    echo "<td>";
                    echo "<form method='POST' style='display:inline;'>";
                    echo "<input type='hidden' name='vazifa_id' value='" . $row['id'] . "'>";
                    echo "<button type='submit' name='vazifa_ochirish'>O‘chirish</button>";
                    echo "</form>";
                    echo "</td>";
                    echo "</tr>";
                }
                ?>
            </table>
            
            <?php if ($_SESSION['admin']): ?>
                <h2>Admin Paneli</h2>
                <table>
                    <tr>
                        <th>Foydalanuvchi ID</th>
                        <th>Foydalanuvchi nomi</th>
                        <th>Vazifalar soni</th>
                    </tr>
                    <?php
                    $sql = "SELECT u.id, u.foydalanuvchi_nomi, COUNT(v.id) as vazifa_soni 
                            FROM foydalanuvchilar u 
                            LEFT JOIN vazifalar v ON u.id = v.foydalanuvchi_id 
                            GROUP BY u.id";
                    $result = $conn->query($sql);
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['id'] . "</td>";
                        echo "<td>" . $row['foydalanuvchi_nomi'] . "</td>";
                        echo "<td>" . $row['vazifa_soni'] . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </table>
            <?php endif; ?>
            
            <form method="POST" action="chiqish.php">
                <button type="submit">Chiqish</button>
            </form>
        <?php endif; ?>
    </div>
    
    <script>
        // Formani tekshirish (JavaScript orqali)
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const inputs = form.querySelectorAll('input[required]');
                let valid = true;
                inputs.forEach(input => {
                    if (!input.value.trim()) {
                        valid = false;
                        input.style.border = '1px solid red';
                    } else {
                        input.style.border = '';
                    }
                });
                if (!valid) {
                    e.preventDefault();
                    alert('Iltimos, barcha majburiy maydonlarni to‘ldiring!');
                }
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); // Ma'lumotlar bazasi ulanishini yopish ?>
```