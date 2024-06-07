<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User-Specific Birthday Storage</title>
    <style>
        body {
            background: url('https://example.com/chand-aur-tare.jpg') no-repeat center center fixed;
            background-size: cover;
            color: white;
            font-family: Arial, sans-serif;
        }
        .form-container {
            background-color: rgba(0, 0, 0, 0.7);
            padding: 20px;
            border-radius: 10px;
            max-width: 400px;
            margin: auto;
            margin-top: 100px;
        }
        input[type="text"], input[type="date"] {
            width: calc(100% - 22px);
            padding: 10px;
            margin-bottom: 10px;
            border: none;
            border-radius: 5px;
        }
        button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        #login-btn {
            background-color: #4CAF50;
            color: white;
        }
        #logout-btn {
            background-color: #f44336;
            color: white;
        }
        #submit-btn {
            background-color: #4CAF50;
            color: white;
        }
        #search-btn {
            background-color: #2196F3;
            color: white;
        }
        #delete-btn {
            background-color: #f44336;
            color: white;
        }
        #refresh-btn {
            background-color: #f44336;
            color: white;
            margin-left: 10px;
        }
        button:hover {
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <?php
        session_start();

        function loadUserData() {
            if (file_exists('user_data.json')) {
                $json_data = file_get_contents('user_data.json');
                return json_decode($json_data, true);
            }
            return [];
        }

        function saveUserData($data) {
            file_put_contents('user_data.json', json_encode($data));
        }

        $users = loadUserData();

        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'logout') {
            session_destroy();
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }

        if (!isset($_SESSION['username'])) {
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username'])) {
                $username = strtolower(trim($_POST['username']));
                $birthday = $_POST['birthday'];
                $_SESSION['username'] = $username;
                if (!isset($users[$username])) {
                    // Generate unique user ID
                    $userId = uniqid('user_');
                    $users[$username] = [
                        'id' => $userId,
                        'birthday' => $birthday,
                        'data' => []
                    ];
                    saveUserData($users);
                }
            } else {
                echo '<h1>Login</h1>
                <form method="post" action="">
                    <input type="text" id="username" name="username" placeholder="Enter username" required>
                    <input type="date" id="birthday" name="birthday" required>
                    <button type="submit" id="login-btn">Login</button>
                </form>';
                exit;
            }
        }

        $username = $_SESSION['username'];
        $userId = $users[$username]['id'];

        // Ensure the 'data' key exists and is an array
        if (!isset($users[$username]['data']) || !is_array($users[$username]['data'])) {
            $users[$username]['data'] = [];
        }
        
        $user_data = &$users[$username]['data'];

        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['name'])) {
            $name = strtolower(trim($_POST['name']));
            $action = $_POST['action'];

            if ($action == "store") {
                $birthday = $_POST['birthday'];
                if (!empty($birthday)) {
                    $user_data[$name] = $birthday;
                    $users[$username]['data'] = $user_data;
                    saveUserData($users);
                    echo "<p>Stored " . ucfirst($name) . "'s birthday: " . $birthday . "</p>";
                } else {
                    echo "<p>Please enter a valid date of birth.</p>";
                }
            } elseif ($action == "search") {
                if (isset($user_data[$name])) {
                    echo "<p>" . ucfirst($name) . "'s birthday is: " . $user_data[$name] . "</p>";
                } else {
                    echo "<p>Name not found in the list.</p>";
                }
            } elseif ($action == "delete") {
                if (isset($user_data[$name])) {
                    unset($user_data[$name]);
                    $users[$username]['data'] = $user_data;
                    saveUserData($users);
                    echo "<p>Deleted " . ucfirst($name) . "'s birthday.</p>";
                } else {
                    echo "<p>Name not found in the list.</p>";
                }
            }
        }
        ?>

        <h1>Enter Name and Birthday</h1>
        <form method="post" action="">
            <input type="text" id="name" name="name" placeholder="Enter name" required>
            <input type="date" id="birthday" name="birthday">
            <button type="submit" id="submit-btn" name="action" value="store">Store</button>
            <button type="submit" id="search-btn" name="action" value="search">Search</button>
            <button type="submit" id="delete-btn" name="action" value="delete">Delete</button>
            <button type="button" id="refresh-btn" onclick="window.location.href='';">Refresh</button>
            <p>ID: <?php echo $userId; ?></p>
        </form>
        <form method="post" action="">
            <input type="hidden" name="action" value="logout">
            <button type="submit" id="logout-btn">Logout</button>
        </form>
    </div>
</body>
</html>
