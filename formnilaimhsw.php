<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Input Nilai Mahasiswa</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
            background-color: #f9f9f9;
            color: #333;
        }
        .container {
            max-width: 400px;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h2 {
            color: #2c3e50;
            margin-top: 0;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        input[type="submit"] {
            background-color: #2c3e50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #34495e;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Form Input Nilai Mahasiswa</h2>
    <form method="post" action="insertnilaimhsw.php">
        <label for="nim">NIM:</label>
        <input type="text" id="nim" name="nim" maxlength="10" required>
        
        <label for="namalengkap">Nama Lengkap:</label>
        <input type="text" id="namalengkap" name="namalengkap" maxlength="50" required>
        
        <label for="nilaiakhir">Nilai Akhir (1 - 100):</label>
        <input type="number" id="nilaiakhir" name="nilaiakhir" step="0.01" min="1" max="100" required>
        
        <input type="submit" value="Simpan Data">
    </form>
</div>

</body>
</html>