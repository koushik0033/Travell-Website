<?php

session_start();
include('includes/config.php'); // Assuming you have a config.php file for database connection

// Check if user is logged in and necessary session variables are set
if (!isset($_SESSION['login']) || !isset($_SESSION['p_id'])) {
    die('Customer email or Package ID not set in session.');
}

if (isset($_POST['submit2'])) {
    $stmt = "SELECT * FROM tblusers WHERE EmailId = ?";
    if ($stmt = $dbh->prepare($stmt)) {
        $stmt->bindParam(1, $_SESSION['login'], PDO::PARAM_STR);
        $stmt->execute();
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($customer) {
            // Fetch package price using package ID
            $package_stmt = "SELECT PackagePrice FROM tbltourpackages WHERE PackageId = ?";
            if ($package_stmt = $dbh->prepare($package_stmt)) {
                $package_stmt->bindParam(1, $_SESSION['p_id'], PDO::PARAM_INT);
                $package_stmt->execute();
                $package = $package_stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($package) {
                    // Initiate payment request to Instamojo
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, 'https://test.instamojo.com/api/1.1/payment-requests/');
                    curl_setopt($ch, CURLOPT_HEADER, FALSE);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        "X-Api-Key:test_4c3f885f6b8a31b8f8619b56c8a",
                        "X-Auth-Token:test_7675546fd2425840367113929e8"
                    ));
                    
                    $payload = array(
                        'purpose' => 'BJJ Booking',
                        'amount' => $package['PackagePrice'],
                        'phone' => $customer['MobileNumber'],
                        'buyer_name' => $customer['FullName'],
                        'redirect_url' => 'http://localhost/bjj/package-details.php?pkgid=' . $_SESSION['p_id'],
                        'send_email' => true,
                        'send_sms' => true,
                        'email' => $customer['EmailId'],
                        'allow_repeated_payments' => false
                    );
                    
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
                    $response = curl_exec($ch);
                    curl_close($ch);
                    
                    $response = json_decode($response);
                    
                    if (isset($response->payment_request->longurl)) {
                        $_SESSION['TID'] = $response->payment_request->id;
                        
                        // Insert booking details into database
                        $pid = intval($_SESSION['p_id']);
                        $useremail = $_SESSION['login'];
                        $fromdate = $_POST['fromdate'];
                        $todate = $_POST['todate'];
                        $comment = $_POST['comment'];
                        $status = 0;
                        $sql = "INSERT INTO tblbooking(PackageId, UserEmail, FromDate, ToDate, Comment, status) VALUES (:pid, :useremail, :fromdate, :todate, :comment, :status)";
                        $query = $dbh->prepare($sql);
                        $query->bindParam(':pid', $pid, PDO::PARAM_STR);
                        $query->bindParam(':useremail', $useremail, PDO::PARAM_STR);
                        $query->bindParam(':fromdate', $fromdate, PDO::PARAM_STR);
                        $query->bindParam(':todate', $todate, PDO::PARAM_STR);
                        $query->bindParam(':comment', $comment, PDO::PARAM_STR);
                        $query->bindParam(':status', $status, PDO::PARAM_STR);
                        $query->execute();
                        $lastInsertId = $dbh->lastInsertId();
                        
                        if ($lastInsertId) {
                            $msg = "Booked Successfully";
                        } else {
                            $error = "Something went wrong. Please try again";
                        }
                        
                        // Redirect to payment page
                        header("Location: " . $response->payment_request->longurl);
                        exit();
                    } else {
                        // Handle error
                        echo 'Payment request failed. Please try again.';
                    }
                } else {
                    echo 'Invalid Package ID';
                }
            } else {
                echo 'Failed to prepare package query';
            }
        } else {
            echo 'Invalid Customer Email';
        }
    } else {
        echo 'Failed to prepare customer query';
    }
}
?>







// session_start();
// include('includes/config.php');

// // Check if user is logged in and necessary session variables are set
// if (!isset($_SESSION['login']) || !isset($_SESSION['p_id'])) {
//     die('Customer email or Package ID not set in session.');
// }

// // Fetch customer details using email
// $stmt = "SELECT * FROM tblusers WHERE EmailId = ?";
// if ($stmt = $con->prepare($stmt)) {
//     $stmt->bind_param("s", $_SESSION['login']);
//     $stmt->execute();
//     $res = $stmt->get_result();
    
//     if ($res->num_rows > 0) {
//         $customer = $res->fetch_assoc();
        
//         // Fetch package price using package ID
//         $package_stmt = "SELECT PackagePrice FROM tbltourpackages WHERE PackageId = ?";
//         if ($package_stmt = $con->prepare($package_stmt)) {
//             $package_stmt->bind_param("i", $_SESSION['p_id']);
//             $package_stmt->execute();
//             $package_res = $package_stmt->get_result();
            
//             if ($package_res->num_rows > 0) {
//                 $package = $package_res->fetch_assoc();
                
//                 // Initiate payment request to Instamojo
//                 $ch = curl_init();
//                 curl_setopt($ch, CURLOPT_URL, 'https://test.instamojo.com/api/1.1/payment-requests/');
//                 curl_setopt($ch, CURLOPT_HEADER, FALSE);
//                 curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
//                 curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
//                 curl_setopt($ch, CURLOPT_HTTPHEADER, array(
//                     "X-Api-Key:test_4c3f885f6b8a31b8f8619b56c8a",
//                     "X-Auth-Token:test_7675546fd2425840367113929e8"
//                 ));
                
//                 $payload = array(
//                     'purpose' => 'GO GREEN PRODUCT',
//                     'amount' => $package['price'],
//                     'phone' => $customer['phone'],
//                     'buyer_name' => $customer['name'],
//                     'redirect_url' => 'http://localhost/bjj/package-details.php?pkgid='.$_SESSION['p_id'],
//                     'send_email' => true,
//                     'send_sms' => true,
//                     'email' => $customer['email'],
//                     'allow_repeated_payments' => false
//                 );
                
//                 curl_setopt($ch, CURLOPT_POST, true);
//                 curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
//                 $response = curl_exec($ch);
//                 curl_close($ch);
                
//                 $response = json_decode($response);
                
//                 if (isset($response->payment_request->longurl)) {
//                     $_SESSION['TID'] = $response->payment_request->id;
//                     header("Location: " . $response->payment_request->longurl);
//                     exit();
//                 } else {
//                     // Handle error
//                     echo 'Payment request failed. Please try again.';
//                 }
//             } else {
//                 echo 'Invalid Package ID';
//             }
//         } else {
//             echo 'Failed to prepare package query';
//         }
//     } else {
//         echo 'Invalid Customer Email';
//     }
// } else {
//     echo 'Failed to prepare customer query';
// }

// if(isset($_POST['submit2']))
// {
// $pid=intval($_SESSION['p_id']);
// $useremail=$_SESSION['login'];
// $fromdate=$_POST['fromdate'];
// $todate=$_POST['todate'];
// $comment=$_POST['comment'];
// $status=0;
// $sql="INSERT INTO tblbooking(PackageId,UserEmail,FromDate,ToDate,Comment,status) VALUES(:pid,:useremail,:fromdate,:todate,:comment,:status)";
// $query = $dbh->prepare($sql);
// $query->bindParam(':pid',$pid,PDO::PARAM_STR);
// $query->bindParam(':useremail',$useremail,PDO::PARAM_STR);
// $query->bindParam(':fromdate',$fromdate,PDO::PARAM_STR);
// $query->bindParam(':todate',$todate,PDO::PARAM_STR);
// $query->bindParam(':comment',$comment,PDO::PARAM_STR);
// $query->bindParam(':status',$status,PDO::PARAM_STR);
// $query->execute();
// $lastInsertId = $dbh->lastInsertId();
// if($lastInsertId)
// {
// $msg="Booked Successfully";
// }
// else 
// {
// $error="Something went wrong. Please try again";
// }

// }

?>