<?php
    session_start();
    include("autoloader.php");
    //Process Login
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $user = $_POST["user"];
        $password = $_POST["password"];
        //array to hold the errors
        $errors = array();
        
        if(filter_var($user, FILTER_VALIDATE_EMAIL)){
            $login_query = "SELECT * FROM user WHERE email=?";
        }
        else{
            $login_query = "SELECT * FROM user WHERE phone=?";
        }
        $db = new Database();
        $connection = $db->getConnection();
            
        $statement = $connection->prepare($login_query);
        $statement->bind_param("s", $user);
        $statement->execute();
        $result = $statement->get_result();
        
        //Check if user is registered
        if($result->num_rows > 0){
            
            //Set cookie for user
            
            if(isset($_POST["rememberMe"])){
                //setcookie("user", $user, time()+60*60*7);
                echo "Remember User";
                $expiry = time()+60*60*7;
                $_SESSION["expiry"] = $expiry;
            }
            if($_SESSION["expiry"]){
                $now = time();
                $diff = $now - $_SESSION["expiry"];
                if($diff>0){
                    // 
                    unset($_SESSION["expiry"]);
                    //get user to login again
                    unset($_SESSION["user_email"]);
                    $needtologin = true;
                }
            }
            
            $userdata = $result->fetch_assoc();
            //check for password macthing
            $stored = $userdata["password"];
            $user_id = $userdata["id"];
            $user_firstName = $userdata["first_name"];
            $user_lastName = $userdata["last_name"];
            $user_email = $userdata["email"];
            $level = $userdata["level"];
            
            //Verify password
            if(password_verify($password, $stored)){
                echo "Welcome $user_firstName!";
                $_SESSION["user_email"] = $user_email;
                $_SESSION["user_firstName"] = $user_firstName;
                $_SESSION["level"] = $level;
                
            }
            else{
                echo "Wrong credentials supplied";
            }
        }
        else{
            echo "Account does not exist!";
        }
    }
    else{
        echo "<center><p class=\"text-center\">Please fill out all fields</p></center>";
    }
?>