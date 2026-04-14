<?php
session_start();
include('config/constants.php');

interface PaymentValidationInterface {
    public function validateAllFields($data);
}

class PaymentValidator extends BaseManager implements PaymentValidationInterface {
    private $data = [];

    public function __construct($db = null) {
        parent::__construct($db);
    }

    public function setData(array $data) {
        $this->data = $data;
        return $this;
    }

    public function sanitize($value) {
        return parent::sanitize($value);
    }

    public function validateAmount($amount) {
        if (empty($amount)) {
            return "Amount is required";
        }
        if (!is_numeric($amount)) {
            return "Amount must be a number";
        }
        return null;
    }
    
    public function validatePhone($phone) {
        if (!is_numeric($phone)) {
            return "Phone must be a number";
        }
        return null;
    }
    
    public function validateEmail($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "Email is not valid";
        }
        return null;
    }
    
    public function validateAllFields($data) {
        $data = empty($data) ? $this->data : $data;
        $errors = [];
        
        if (empty($data['amount']) || empty($data['purchase_order_id']) || empty($data['purchase_order_name']) || 
            empty($data['name']) || empty($data['email']) || empty($data['phone'])) {
            return ["All fields are required"];
        }
        
        if ($error = $this->validateAmount($data['amount'])) {
            $errors[] = $error;
        }
        if ($error = $this->validatePhone($data['phone'])) {
            $errors[] = $error;
        }
        if ($error = $this->validateEmail($data['email'])) {
            $errors[] = $error;
        }
        
        return $errors;
    }
}

if (isset($_POST['submit'])) {
    $amount = isset($_POST['inputAmount4']) ? ((float)$_POST['inputAmount4'] * 100) : 0; // Convert to paisa
    $purchase_order_id = $_POST['inputPurchasedOrderId4'] ?? '';
    $purchase_order_name = $_POST['inputPurchasedOrderName'] ?? '';
    $name = $_POST['inputName'] ?? '';
    $email = $_POST['inputEmail'] ?? '';
    $phone = $_POST['inputPhone'] ?? '';

    $validator = new PaymentValidator();
    $errors = $validator
        ->setData([
        'amount' => $amount,
        'purchase_order_id' => $purchase_order_id,
        'purchase_order_name' => $purchase_order_name,
        'name' => $validator->sanitize($name),
        'email' => $validator->sanitize($email),
        'phone' => $validator->sanitize($phone)
    ])
        ->validateAllFields([]);

    if (empty($errors)) {
        $postFields = array(
            "return_url" => "http://localhost/OPS/index.php?order_status=success",
            "website_url" => "http://localhost/khalti-payment/",
            "amount" => $amount,
            "purchase_order_id" => $purchase_order_id,
            "purchase_order_name" => $purchase_order_name,
            "customer_info" => array(
                "name" => $name,
                "email" => $email,
                "phone" => $phone
            )
        );

        $jsonData = json_encode($postFields);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://a.khalti.com/api/v2/epayment/initiate/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $jsonData,
            CURLOPT_HTTPHEADER => array(
                'Authorization: key live_secret_key_68791341fdd94846a146f0457ff7b455', // Replace with your actual key
                'Content-Type: application/json',
            ),
        ));

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            echo 'Error:' . curl_error($curl);
        } else {
            $responseArray = json_decode($response, true);

            if (isset($responseArray['error'])) {
                echo 'Error: ' . $responseArray['error'];
            } elseif (isset($responseArray['payment_url'])) {
                // Redirect the user to the payment page
                header('Location: ' . $responseArray['payment_url']);
                exit();
            } else {
                echo 'Unexpected response: ' . $response;
            }
        }

        curl_close($curl);
    } else {
        echo 'Validation error: ' . implode(', ', $errors);
    }
}
