<?php include('partials/menu.php');

class PasswordUpdateManager extends BaseManager {
    public function __construct($db = null) {
        parent::__construct($db);
    }
    
    public function getAdminById($id) {
        $sql = "SELECT * FROM tbl_admin WHERE id=?";
        $stmt = $this->db->prepare($sql);
        $this->db->bind($stmt, "i", $id);
        $this->db->execute($stmt);
        $res = $this->db->getResult($stmt);
        
        if ($this->db->numRows($res) == 1) {
            return $this->db->fetchAssoc($res);
        }
        return null;
    }
    
    public function verifyPassword($inputPassword, $storedPasswordHash) {
        return password_verify($inputPassword, $storedPasswordHash);
    }
    
    public function updatePassword($id, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $sql = "UPDATE tbl_admin SET password=? WHERE id=?";
        $stmt = $this->db->prepare($sql);
        $this->db->bind($stmt, "si", $hashedPassword, $id);
        return $this->db->execute($stmt);
    }
}

$id = isset($_GET['id']) ? $_GET['id'] : null;
$passwordUpdateManager = new PasswordUpdateManager();

// Check if form is submitted
if(isset($_POST['submit'])) {
    $id = $_POST['id'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Get admin
    $admin = $passwordUpdateManager->getAdminById($id);
    
    if ($admin) {
        if ($passwordUpdateManager->verifyPassword($current_password, $admin['password'])) {
            if ($new_password == $confirm_password) {
                if ($passwordUpdateManager->updatePassword($id, $new_password)) {
                    $_SESSION['change-password'] = "Password Changed Successfully";
                    header('location:'.SITEURL.'admin/manage-admin.php');
                    exit;
                } else {
                    $_SESSION['change-password'] = "Failed to Change Password";
                    header('location:'.SITEURL.'admin/manage-admin.php');
                    exit;
                }
            } else {
                $_SESSION['password-not-match'] = "Passwords didn't match";
                header('location:'.SITEURL.'admin/manage-admin.php');
                exit;
            }
        } else {
            $_SESSION['current-password-error'] = "Incorrect Current Password";
            header('location:'.SITEURL.'admin/manage-admin.php');
            exit;
        }
    } else {
        $_SESSION['user-not-found'] = "User Not Found";
        header('location:'.SITEURL.'admin/manage-admin.php');
        exit;
    }
}

<?php include('partials/footer.php')?>
