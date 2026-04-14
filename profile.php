<?php 
include('config/constants.php');

class UserProfileManager extends BaseManager {
    public function __construct($db = null) {
        parent::__construct($db);
    }
    
    public function getUserById($userId) {
        $sql = "SELECT * FROM tbl_users WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        $this->db->bind($stmt, "i", $userId);
        $this->db->execute($stmt);
        $res = $this->db->getResult($stmt);
        
        if ($res && $this->db->numRows($res) > 0) {
            return $this->db->fetchAssoc($res);
        }
        return null;
    }
    
    public function validateInput($data, $userId) {
        $errors = [];
        
        if (empty($data['full_name']) || empty($data['username']) || empty($data['phone']) || empty($data['email']) || empty($data['address'])) {
            $errors[] = 'All fields are required.';
        }
        
        $sql = "SELECT * FROM tbl_users WHERE username = ? AND user_id != ?";
        $stmt = $this->db->prepare($sql);
        $this->db->bind($stmt, "si", $data['username'], $userId);
        $this->db->execute($stmt);
        $res = $this->db->getResult($stmt);
        
        if ($this->db->numRows($res) > 0) {
            $errors[] = 'Username already exists.';
        }
        
        $sql = "SELECT * FROM tbl_users WHERE email = ? AND user_id != ?";
        $stmt = $this->db->prepare($sql);
        $this->db->bind($stmt, "si", $data['email'], $userId);
        $this->db->execute($stmt);
        $res = $this->db->getResult($stmt);
        
        if ($this->db->numRows($res) > 0) {
            $errors[] = 'Email already exists.';
        }
        
        if (!preg_match('/^[0-9]{10,15}$/', $data['phone'])) {
            $errors[] = 'Phone number must be between 10 and 15 digits.';
        }
        
        return $errors;
    }
    
    public function updateProfile($userId, $fullName, $username, $phone, $email, $address) {
        $sql = "UPDATE tbl_users SET 
            full_name = ?,
            username = ?,
            phone = ?,
            email = ?,
            address = ?
            WHERE user_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $this->db->bind($stmt, "sssssi", $fullName, $username, $phone, $email, $address, $userId);
        return $this->db->execute($stmt);
    }
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userProfile = new UserProfileManager();
$userId = $_SESSION['user_id'];
$user = $userProfile->getUserById($userId);
$errors = [];

if (!$user) {
    echo "User not found.";
    exit();
}

$fullName = $user['full_name'];
$username = $user['username'];
$phone = $user['phone'];
$email = $user['email'];
$address = $user['address'];

// Handle form submission for updating user details
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $newData = [
        'full_name' => htmlspecialchars($_POST['full_name']),
        'username' => htmlspecialchars($_POST['username']),
        'phone' => htmlspecialchars($_POST['phone']),
        'email' => htmlspecialchars($_POST['email']),
        'address' => htmlspecialchars($_POST['address']),
    ];
    
    $errors = $userProfile->validateInput($newData, $userId);
    
    if (empty($errors)) {
        if ($userProfile->updateProfile($userId, $newData['full_name'], $newData['username'], $newData['phone'], $newData['email'], $newData['address'])) {
            $_SESSION['full_name'] = $newData['full_name'];
            $_SESSION['user'] = $newData['username'];
            echo "<script>alert('Profile updated successfully.'); window.location.href='profile.php';</script>";
        } else {
            $errors[] = 'Failed to update profile.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <style>
        .profile-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background-color: #f9f9f9;
            position: relative;
        }

        .profile-header {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            margin-bottom: 20px;
        }

        .profile-logo {
            background-color: #007bff;
            color: #fff;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-right: 10px;
            cursor: pointer;
        }

        .profile-logo:hover {
            background-color: #0056b3;
        }

        .profile-logo span {
            font-weight: bold;
        }

        form {
            display: flex;
            flex-direction: column;
            position: relative;
        }

        input[type="text"], input[type="email"], input[type="tel"] {
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        input[type="submit"] {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            position: relative;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        .error {
            color: red;
            margin-bottom: 10px;
        }

        .confirmation-box {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            z-index: 10;
            width: 100%;
            margin-top: 5px;
        }

        .confirmation-box button {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 5px;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 5px;
        }

        .confirmation-box button:hover {
            background-color: #0056b3;
        }

        .confirmation-box button.cancel {
            background-color: #f44336;
        }

        .confirmation-box button.cancel:hover {
            background-color: #c62828;
        }
    </style>
    <script>
        function showConfirmationBox(event) {
            event.preventDefault(); // Prevent the form from submitting immediately
            
            document.getElementById('confirmationBox').style.display = 'block';
        }

        function confirmUpdate() {
            document.getElementById('profileForm').submit();
        }

        function cancelUpdate() {
            document.getElementById('confirmationBox').style.display = 'none';
        }
    </script>
</head>
<body>
    <div class="profile-container">
        <div class="profile-header">
            <div class="profile-logo">
                <span>P</span>
            </div>
            <a href="profile.php" style="text-decoration: none; color: #007bff;">Edit Profile</a>
        </div>

        <h1>Edit Profile</h1>

        <?php if (!empty($errors)) { ?>
            <div class="error">
                <?php foreach ($errors as $error) { echo "<p>$error</p>"; } ?>
            </div>
        <?php } ?>

        <form id="profileForm" action="" method="POST">
            <label for="full_name">Full Name:</label>
            <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($full_name); ?>" required>
            
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
            
            <label for="phone">Phone:</label>
            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required>
            
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            
            <label for="address">Address:</label>
            <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($address); ?>" required>
            
            <input type="submit" value="Update Profile" onclick="showConfirmationBox(event)">
            
            <!-- Confirmation Box -->
            <div id="confirmationBox" class="confirmation-box">
                <p>Are you sure you want to update your profile?</p>
                <button type="button" onclick="confirmUpdate()">Yes</button>
                <button type="button" class="cancel" onclick="cancelUpdate()">No</button>
            </div>
        </form>
    </div>
</body>
</html>
