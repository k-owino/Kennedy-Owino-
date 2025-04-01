<?php

session_start();//begins a session

//CREATE A DATABASE CONNECTION
	$conn=mysqli_connect("localhost", "root", " ", "rentalmanagementsystem");

/*----------------------------------USER REGISTRATION BEGINS HERE(register.php FILE)----------------------------------------*/
	
$user_id="";
$id="";	
$fname="";
$lname="";
$gender="";
$natID="";
$email="";
$phone="";
$username="";
$house="";
$pwd="";

$errors =array();


if(isset($_POST['Submit'])){
	register();
}

//Register User
function register(){
	global $conn,$errors,  $fname, $lname, $gender, $natID, $email, $phone, $username, $pwd;

	$fname=e($_POST['fname']);
	$lname=e($_POST['lname']);
	$gender=e($_POST['gender']);
	$natID=e($_POST['natID']);
	$email=e($_POST['email']);
	$phone=e($_POST['phone']);
	$username=e($_POST['username']);
	$pwd=e($_POST['pwd']);
	
 				
				if(empty($fname)){
					array_push($errors, "First name  cannot be blank");
				}

				if(empty($lname)){
					array_push($errors, "Last Name  cannot be blank");
				}

				if(empty($gender)){
					array_push($errors, "gender cannot be blank");
				}

				if(empty($natID)){
					array_push($errors, "National ID  cannot be blank");
				}

				if(empty($phone)){
					array_push($errors, "phone  cannot be blank");
				}

				if(empty($pwd)){
					array_push($errors, "password  cannot be blank");
				}
				else{
					//$username=$_POST['username'];
					//$email=$_POST['email'];
			//check if Username or Email already exists in the database
			$sql_u="SELECT * FROM tenants WHERE Username='$username'";
			$sql_e="SELECT * FROM tenants WHERE Email='$email'";

			$res_u=mysqli_query($conn, $sql_u);
			$res_e=mysqli_query($conn, $sql_e);
			
			if(mysqli_num_rows($res_u)>0){
				$_SESSION['username']=$username;
				array_push($errors, "Username already exists");
			}
			if(mysqli_num_rows($res_e)>0){
				$_SESSION['email']=$email;
				array_push($errors, "Email already exists");
			}
			elseif(count($errors) == 0){
					$pwd=md5($_POST['pwd']);//encrypt database before storing in database
			
					//INSERT CUSTOMERS DETAILS INTO tenants TABLE in the DB
					$query="INSERT INTO tenants (First_Name,Last_Name,Gender,National_ID,Email,Phone,Username,Password,user_type)  VALUES ('$fname','$lname','$gender','$natID','$email','$phone','$username','$pwd','user')";
					mysqli_query($conn,$query);

					
					//INSERT CUSTOMERS' LOGIN DETAILS INTO users TABLE in the DB
					$sql="INSERT INTO users (tenant,Username,Password,user_type)  VALUES ((SELECT tenant_id FROM tenants WHERE First_Name='$fname' AND Last_Name='$lname'),'$username','$pwd','user')";
					mysqli_query($conn,$sql);
							

						$_SESSION['username']=$username;
						$_SESSION['success']="You've successfully registered";
						header("Location: SignIn.php");
					
			}}
}

//RETURN USER ARRAY FROM THEIR ID
function getUserById(){
	global $conn, $tenant_id;
	$query="SELECT * FROM tenants WHERE tenant_id='".$_POST['tenant_id']."'";
	$result=mysqli_query($conn,$query);

	$user=mysqli_fetch_assoc($result);
	return $user;
}

//ESCAPE STRING
function e($val){
	global $conn;
	return mysqli_real_escape_string($conn, trim($val));
}

//ERROR HANDLER
function display_error(){
	global $errors;

	if(count($errors)>0){
		echo '<div class="">';
			foreach ($errors as $error) {
			'<small>';
				echo $error.'<br>';
			'</small>';
				
			}
		echo '</div>';
	}
}


//PREVENT USER FROM ACCESSING THE index.php BY URL
function isLoggedIn(){
	if(isset($_SESSION['user'])){
		return true;
	}else{
		return false;
	}
}

//LOGOUT USER
if(isset($_GET['logout'])){
	session_destroy();
	unset($_SESSION['user']);
	header("Location: SignIn.php");
}

/************************************************USER LOGIN BEGINS HERE(SignIn.php FILE)*****************************************/
//call login() function if submit is clicked
if(isset($_POST['login'])){
	login();
	$logged_in=true;
}

//login User
function login(){
	global $conn, $username, $pwd,$errors;
	

	//grab form variables
	$id="";
	$tenant="";
	$username=e($_POST['username']);
	$pwd=e($_POST['pwd']);
	
	//fill the fields
	if(empty($username)){
		array_push($errors, "username is required");
	}
	if(empty($pwd)){
		array_push($errors, "password is required");
	}

//LOGIN CHECK IF USER IS ADMIN IN THE USERS TABLE?IF NOT ADMIN,REDIRECT USER TO CUSTOMER PORTAL  
	if(count($errors)==0){
		$pwd=md5($pwd);
		$sql="SELECT * FROM users WHERE Username='$username' AND Password='$pwd'";
		$result=mysqli_query($conn, $sql);

	if(mysqli_num_rows($result)>0){
		$logged_in_user=mysqli_fetch_assoc($result);

			if($logged_in_user['user_type'] == 'admin'){
				$_SESSION['user']=$logged_in_user;
				$_SESSION['username']=$username;
				$_SESSION['success']="Successful User Log In";
				header("Location: admin/adminpanel.php");
			}else{
				$_SESSION['user']=$logged_in_user;
				$_SESSION['username']=$username;
				$_SESSION['success']="Successful User Log In";
				header("Location: index.php");
			}
		}else{
			array_push($errors, "wrong username or password combination");
		}
		
	}

}

//CHECK IF USER IS ADMIN
function isAdmin(){
	if(isset($_SESSION['user']) && $_SESSION['user']['user_type']=='admin'){
		return true;
	}else{
		return false;
	}
}

//LOGOUT USER (TENANT) FROM index.php
if(isset($_GET['logout'])){
	session_destroy();
	unset($_SESSION['user']);
	header("Location: SignIn.php");
}

/***********************************************************USERS UPLOAD PROFILE************************************************/
	//ADMIN UPLOAD PROFILE PICTURE(userprofile.php FILE)
		if(isset($_POST['save_profile'])){
		   move_uploaded_file($_FILES['profileImage']['tmp_name'],'../image/'.$_FILES['profileImage']['name']);

		   $sql="UPDATE tenants SET image='".$_FILES['profileImage']['name']."' WHERE Username='".$_SESSION['username']."'";
			mysqli_query($conn,$sql);
				}

	 //USER UPLOAD PROFILE PICTURE(system_user_profile.php FILE)
        if(isset($_POST['profile'])){
          move_uploaded_file($_FILES['profileImage']['tmp_name'],'image/'.$_FILES['profileImage']['name']);

          $sql="UPDATE tenants SET image='".$_FILES['profileImage']['name']."' WHERE Username='".$_SESSION['username']."'";
          mysqli_query($conn,$sql);
        }

/***************************USER TO SELECT A HOUSE(../services/houseApplication.php FILE)**************************************/
$tenant_id="";
$house_id="";
$house="";
$status='occupied';
$user_type="";
$fname="";
$lname="";

if(isset($_POST['submit'])){
	$checkbox=$_POST['radio'];

	foreach($checkbox as $value){

			//UPDATE RECORDS IN THE tenants & house TABLES IN THE DB
			$query="UPDATE tenants,house SET house='$value',status='occupied' WHERE Username='".$_SESSION['username']."' AND house_id='$value'";
			mysqli_query($conn,$query);							 
	}
}


/*******************************PAYMENT MODULE ON TENANT PORTAL(../services/payment.php FILE)***********************************/
$firstname="";
$lastname="";
$house_number="";
$pay="";
$invoice_no="";

//RETRIEVE AND COMBINE RECORDS FROM house, invoice & tenants TABLES IN THE DB
$sql="SELECT * FROM (SELECT * FROM house) AS A JOIN  (SELECT * FROM invoice) AS B JOIN (SELECT * FROM tenants WHERE Username='".$_SESSION['username']."') AS C ON A.house_id=C.house AND C.tenant_id=B.tenant ";
$res=mysqli_query($conn,$sql);

//DISPLAYS SELECTED RECORDS FROM tenants,house & invoice TABLES IN THE DB
while($row=mysqli_fetch_array($res)){
		$firstname=$row['First_Name'];
		$lastname=$row['Last_Name'];
		$house_number=$row['house_number'];
		$pay=$row['rent'];
		$invoice_no=$row['invoice_number'];

	$_SESSION['success']="Rent Payment Successful";
	header("Location: payment.php");

	}


/*************************TENANT SUBMIT PAYMENT DATA TO DB(../services/payment.php FILE)***********************************/
if(isset($_POST['payment'])){
	
	$id=$_POST['id'];
	$invoice_num=$_POST['invoice'];
	$tenant=$_POST['tenant'];
	$house=$_POST['house'];
	$Amount=$_POST['Amount'];
	$paid=$_POST['paid'];
	$balance=$_POST['balance'];
	$transID=$_POST['transID'];
	$status=$_POST['pay_status'];
	$date=$_POST['date_of_payment'];
	$pay_state=true;


		if($balance==0){

			//INSERT DATA INTO payment TABLE IN THE DB
			$query="INSERT INTO payment(invoice,tenant,house,amount,amount_paid,transaction_code,balance,pay_status,date_of_payment) VALUES((SELECT invoice_id FROM invoice WHERE invoice_number='$invoice_no'),(SELECT tenant_id FROM tenants WHERE First_Name='$firstname' AND Last_Name='$lastname'),(SELECT house_id FROM house WHERE house_number='$house_number'),'$Amount','$paid','$transID','$balance','Cleared',CURDATE())";
			mysqli_query($conn,$query);
			
			$_SESSION['success']="payment sent successfully";
			header("Location: payment.php");
		}else{
			$query="INSERT INTO payment(invoice,tenant,house,amount,amount_paid,transaction_code,balance,pay_status,date_of_payment) VALUES((SELECT invoice_id FROM invoice WHERE invoice_number='$invoice_no'),(SELECT tenant_id FROM tenants WHERE First_Name='$firstname' AND Last_Name='$lastname'),(SELECT house_id FROM house WHERE house_number='$house_number'),'$Amount','$paid','$transID','$balance','Pending',CURDATE())";
			mysqli_query($conn,$query);
			$_SESSION['success']="payment sent successfully";
			header("Location: payment.php");
		}


			}	

/*******************************************************INVOICE***************************************************************/
//Retrieve records from Invoice table in the DB	
$sql="SELECT * FROM invoice";
$result=mysqli_query($conn,$sql);

//Delete Records from Invoice Table
if(isset($_GET['del'])){
	$invoice_id=$_GET['del'];
	mysqli_query($conn, "DELETE FROM invoice WHERE invoice_id='$invoice_id' ");
	$_SESSION['success']="Invoice Deleted";
	header("Location: invoice.php");
}

//FETCH THE RECORD UPDATED
/*if(isset($_GET['edit'])){
	$invoice_id=$_GET['edit'];
	$edit_state=true;

	$record=mysqli_query($conn, "SELECT * FROM invoice WHERE invoice_id='$invoice_id' ");

	if(count($record)==1){
	$rec=mysqli_fetch_array($record);
	$invoice_id=$rec['invoice_id'];
	$invoice_number=$rec['invoice_number'];
	$tenant=$rec['tenant_id'];
	$house=$rec['house'];
	$payment=$rec['payment'];
	}
	}*/

													/*ADMINISTRATOR PORTAL*/

/*****************************************TENANTS RECORDS(../admin/tenant.php FILE)********************************************/
//RETRIVE RECORDS FROM tenants & house TABLES IN THE DB
$sql="SELECT * FROM (SELECT * FROM house) AS A JOIN (SELECT * FROM tenants WHERE user_type='user') AS B  ON A.house_id=B.house";
$res=mysqli_query($conn,$sql);


$fname="";
$lname="";
$gender="";
$natID="";
$email="";
$phone="";
$username="";
$pwd="";

/***********************ADMIN ADD NEW USER IN THE SYSTEM(../admin/create_new_user.php FILE)**************************************/
if(isset($_POST['save'])){
	global $conn,$errors,  $fname, $lname, $gender, $natID, $email, $phone, $username, $pwd;

	$fname=$_POST['fname'];
	$lname=$_POST['lname'];
	$gender=$_POST['gender'];
	$natID=$_POST['natID'];
	$email=$_POST['email'];
	$phone=$_POST['phone'];
	$username=$_POST['username'];
	$pwd=$_POST['pwd'];



	           if(empty($fname)){
					array_push($errors, "First name  cannot be blank");
				}

				if(empty($lname)){
					array_push($errors, "Last Name  cannot be blank");
				}

				if(empty($gender)){
					array_push($errors, "gender cannot be blank");
				}

				if(empty($natID)){
					array_push($errors, "National ID  cannot be blank");
				}

				if(empty($phone)){
					array_push($errors, "phone  cannot be blank");
				}

				if(empty($pwd)){
					array_push($errors, "password  cannot be blank");
				}
				else{
					//$username=$_POST['username'];
					//$email=$_POST['email'];*/
			//check if Username or Email already exists in the database
			$sql_u="SELECT * FROM tenants WHERE Username='$username'";
			$sql_e="SELECT * FROM tenants WHERE Email='$email'";

			$res_u=mysqli_query($conn, $sql_u);
			$res_e=mysqli_query($conn, $sql_e);
			
			if(mysqli_num_rows($res_u)>0){
				$_SESSION['username']=$username;
				array_push($errors, "Username already exists");
			}
			if(mysqli_num_rows($res_e)>0){
				$_SESSION['email']=$email;
				array_push($errors, "Email already exists");
			}
				elseif(count($errors) == 0){
					
					$pwd=md5($_POST['pwd']);//encrypt database before storing in database
			
					//INSERT USER PERSONAL DETAILS IN THE tenant TABLE IN THE DB
					$query="INSERT INTO tenants(First_Name,Last_Name,Gender,National_ID,Email,Phone,Username,Password,user_type) VALUES('$fname','$lname','$gender','$natID','$email','$phone','$username','$pwd','user')";
					mysqli_query($conn,$query);

					//INSERT LOGIN CREDENTIALS IN THE users TABLE IN THE DB
					$sql="INSERT INTO users (tenant,Username,Password,user_type)  VALUES ((SELECT tenant_id FROM tenants WHERE First_Name='$fname' AND Last_Name='$lname'),'$username','$pwd','user')";
					mysqli_query($conn,$sql);

						$_SESSION['username']=$username;
						$_SESSION['success']="You've successfully registered";
						header("Location: adminpanel.php");
				}
			}
		}
				

//Delete Tenants Records from Tenants and Payment Table
if(isset($_GET['del'])){
	$id=$_GET['del'];

	mysqli_query($conn,"DELETE FROM tenants WHERE tenant_id='$id'");
	
	$_SESSION['success']="Tenant Record Deleted";
	header("Location: ../tenant.php");
}


//FETCH THE RECORD UPDATED
if(isset($_GET['edit'])){
	$id=$_GET['edit'];
	$edit_state=true;

	$record=mysqli_query($conn, "SELECT * FROM tenants WHERE tenant_id='$id' ");

	if(count($record)==1){
	$rec=mysqli_fetch_array($record);
	$id=$rec['tenant_id'];
	$fname=$rec['First_Name'];
	$lname=$rec['Last_Name'];
	$nat=$rec['National_ID'];
	$gender=$rec['Gender'];
	$email=$rec['Email'];
	$phone=$rec['Phone'];
	$house=$rec['house'];
	}
	}

if(isset($_POST['update'])){
	$id=$_POST['id'];
	$fname=$_POST['fname'];
	$lname=$_POST['lname'];
	$gender=$_POST['gender'];
	$natID=$_POST['natID'];
	$email=$_POST['email'];
	$phone=$_POST['phone'];
	$username=$_POST['username'];
	$pwd=$_POST['pwd'];

	mysqli_query($conn,"UPDATE tenants SET First_Name='$fname', Last_Name='$lname', Gender='$gender', National_ID='$natID', Email='$email',Phone='$phone',Username='$username',Password='$pwd' WHERE tenant_id='$id' ");
	$_SESSION['success']="Tenant Record Updated";
	header("Location: ../tenant.php");
}

/*****************************************GENERATE INVOICE(..admin/invoice_create.php FILE)**************************************/
$invoice_state='';
if(isset($_GET['invoice'])){
	$tenant_id=$_GET['invoice'];
	$invoice_state=true;

	//RETRIEVE & COMBINES RECORDS FROM tenants & house TABLES IN THE DB
	$sql="SELECT * FROM (SELECT  * FROM tenants  WHERE tenant_id='$tenant_id') AS A JOIN (SELECT * FROM  house) AS B ON A.house=B.house_id";
	$result=mysqli_query($conn,$sql);

		//DISPLAYS SELECTED RECORDS FROM tenants & house TABLES IN THE DB
		while($row=mysqli_fetch_array($result)){
			$fname=$row['First_Name'];
			$lname=$row['Last_Name'];
			$house_number=$row['house_number'];
			$rent=$row['rent'];	
			}
		}	

//FETCH THE RECORD UPDATED
if(isset($_GET['edit'])){
	$tenant_id=$_GET['edit'];
	$edit_state=true;

	$record=mysqli_query($conn, "SELECT * FROM tenants WHERE tenant_id='$tenant_id' ");

	/*if(count($record)==1){
	$rec=mysqli_fetch_array($record);
	$tenant_id=$rec['tenant_id'];
	$fname=$rec['First_Name'];
	$lname=$rec['Last_Name'];
	$nat=$rec['National_ID'];
	$gender=$rec['Gender'];
	$email=$rec['Email'];
	$phone=$rec['Phone'];
	$house=$rec['house'];
	}*/
	}

/************************************INVOICE UPLOAD TO THE DB(../admin/uploadInvoice.php FILE)***********************************/
//$sql="SELECT *,First_Name,Last_Name FROM invoice INNER JOIN tenants ON invoice.tenant_id=tenants.tenant_id";

//JOINS THE tenants & invoice TABLES IN THE DB
$sql="SELECT * FROM tenants INNER JOIN invoice ON tenants.tenant_id=invoice.tenant";
$invo=mysqli_query($conn,$sql);

if(isset($_GET['upload'])){
	$tenant_id=$_GET['upload'];

	$sql="SELECT  * FROM tenants  WHERE  tenant_id='$tenant_id'";
	$res=mysqli_query($conn,$sql);

		while($row=mysqli_fetch_array($res)){
			$firstname=$row['First_Name'];
			$lastname=$row['Last_Name'];
		}
}

//UPLOAD INVOICE TO THE invoice TABLE IN THE DB
if(isset($_POST['Upload'])){
	$invoice_no=$_POST['invoice_no'];
	$month_name=$_POST['month_name'];
	$file=$_FILES['file'];

	$fileName=$_FILES['file']['name'];
	$fileTmpName=$_FILES['file']['tmp_name'];


if($fileName){
	$targetfolder="uploads/".$fileName;

	move_uploaded_file($fileTmpName,$targetfolder);


		$query="INSERT INTO invoice(invoice_number,tenant,name,path) VALUES('$invoice_no',(SELECT tenant_id FROM tenants  WHERE  tenant_id='$tenant_id'),'$month_name','$fileName')";
		mysqli_query($conn,$query);

		$_SESSION['success']="Invoice Upload successful";
		header("Location: tenant.php");
	}
}


												/*USER PORTAL*/
/**************************************DOWNLOAD INVOICE FROM DB(../services/invoice_view.php FILE)******************************/
if(isset($_GET['download'])){
	$path=$_GET['download'];

	$sql="SELECT * FROM invoice WHERE path='$path'";
	mysqli_query($conn,$sql);

	header('Content-Type: application/octect-stream');
	header('Content-Disposition: attachment; filename="'.basename($path).'"');
	header('Content-Length: '.filesize($path));
	readfile($path);
}

if(isset($_GET['down'])){
	$path=$_GET['down'];

	$sql="SELECT * FROM invoice WHERE path='$path'";
	mysqli_query($conn,$sql);

	header('Content-Type: application/octect-stream');
	header('Content-Disposition: attachment; filename="'.basename($path).'"');
	header('Content-Length: '.filesize($path));
	readfile($path);
}


//Delete Invoice Record
if(isset($_GET['del'])){
	$invoice_id=$_GET['del'];
	mysqli_query($conn, "DELETE FROM invoice WHERE invoice_id='$invoice_id' ");
	$_SESSION['success']="Invoice Record Deleted";
	header("Location: ../admin/invoice.php");
}

/*******************************DISPLAY INVOICE FOR EACH TENANT(../services/invoice_view.php FILE)*******************************/
$sql="SELECT * FROM (SELECT * FROM tenants WHERE Username='".$_SESSION['username']."') AS A JOIN (SELECT * FROM invoice) AS B  ON A.tenant_id=B.tenant ";
$inv=mysqli_query($conn,$sql);

/**************************************ADMIN PAYMENT MODULE*****************************************************/
	$sql="SELECT * FROM payment INNER JOIN (SELECT * FROM tenants) AS A JOIN (SELECT * FROM house) AS B  ON payment.tenant=A.tenant_id AND payment.house=B.house_id";
	$results=mysqli_query($conn,$sql);

	$tenant="";
	$hnumber="";
	$Amount="";
	$transID="";
	$balance="";
	$todayIs="";
	$pay_id=0;
	$status="";
	$edit_state=false;
	
	
	
	/*if(isset($_POST['save'])){

	$id=$_POST['id'];
	$tenant=$_POST['tenant'];
	$hnumber=$_POST['hnumber'];
	$Amount=$_POST['Amount'];
	$transCode=$_POST['transCode'];
	$amountPaid=$_POST['amountPaid'];
	$balance=$_POST['balance'];

	$query="INSERT INTO payment(tenant,house, amount,transactionID,amount_paid,balance) VALUES('$tenant','$hnumber','$Amount','$transCode', '$amountPaid','$balance')";
	mysqli_query($conn, $query);
	$_SESSION['success']="Payment saved successfully";
	header("Location: paymentTransaction.php");
}*/

//Update House Records
if(isset($_POST['update'])){
	$pay_id=$_POST['pay_id'];
	$tenant=$_POST['tenant'];
	$hnumber=$_POST['hnumber'];
	$Amount=$_POST['Amount'];
	$transCode=$_POST['transCode'];
	$amountPaid=$_POST['amountPaid'];
	$balance=$_POST['balance'];

mysqli_query($conn,"UPDATE payment SET tenant='$tenant', house='$hnumber', amount='$Amount', transactionID='$transCode', amount_paid='$amountPaid', balance='$balance' WHERE pay_id='$pay_id' ");
	$_SESSION['success']="Payment Record Updated";
	header("Location: paymentTransaction.php");
}

//Delete Records
if(isset($_GET['del'])){
	$pay_id=$_GET['del'];
	mysqli_query($conn, "DELETE FROM payment WHERE pay_id='$pay_id' ");
	$_SESSION['success']="Tenant Payment Record Deleted";
	header("Location: paymentTransaction.php");
}

//FETCH THE RECORD UPDATED
if(isset($_GET['edit'])){
	$pay_id=$_GET['edit'];
	$edit_state=true;

	$record=mysqli_query($conn, "SELECT * FROM payment WHERE pay_id='$pay_id' ");
}
/*	if(count($record)==1){
	$rec=mysqli_fetch_array($record);
	$pay_id=$rec['pay_id'];
	$tenant=$rec['tenant'];
	$hnumber=$rec['house'];
	$Amount=$rec['amount'];
	$transCode=$rec['transactionID'];
	$amountPaid=$rec['amount_paid'];
	$balance=$rec['balance'];
	}
	}



//UPDATE THE PAYMENT TABLE
if(isset($_GET['edit'])){
	$pay_id=$_GET['edit'];
	$edit_state=true;

	$record=mysqli_query($conn, "SELECT * FROM payment WHERE pay_id='$pay_id' ");

	if(count($record)==1){
	$rec=mysqli_fetch_array($record);
	$pay_id=$rec['pay_id'];
	$tenant=$rec['tenant'];
	$house=$rec['house'];
	$amount=$rec['amount'];
	$transactionID=$rec['transactionID'];
	$amount_paid=$rec['amount_paid'];
	$balance=$rec['balance'];
	}
	}

/***********************************GENERATE PAYMENT RECEIPT****************************************************/
if(isset($_GET['receipt'])){
	$id=$_GET['receipt'];

	$sql="SELECT * FROM payment INNER JOIN (SELECT * FROM tenants) AS A JOIN (SELECT * FROM house) AS B  ON payment.tenant=A.tenant_id AND payment.house=B.house_id WHERE pay_id='$id'";
	//$sql="SELECT * FROM payment JOIN (SELECT * FROM tenants) AS A ON payment.tenant=A.tenant_id JOIN house ON payment.house=house.house_id JOIN invoice ON payment.invoice=invoice.invoice_id WHERE pay_id='$id'";
	$results=mysqli_query($conn,$sql);

	while($row=mysqli_fetch_array($results)){
			$firstname=$row['First_Name'];
			$lastname=$row['Last_Name'];
			$hnumber=$row['house_number'];
			$paid=$row['amount_paid'];
			//$month=$row['name'];
			$balance=$row['balance'];
		}
}
/************************************UPLOAD RECEIPT TO DB********************************************
$sql="SELECT * FROM receipt INNER JOIN (SELECT * FROM tenants) AS A JOIN (SELECT * FROM payment) AS B ON receipt.tenant=A.tenant_id AND receipt.pay=B.pay_id WHERE receipt_id='$id'";
$invoice=mysqli_query($conn,$sql);


if(isset($_POST['receipt'])){
	$firstname=$_POST['firstname'];
	$lastname=$_POST['lastname'];
	$month_name = $_POST['month_name'];
	
	$name=$_FILES['myfile']['name'];
	$tmp_name=$_FILES['myfile']['tmp_name'];

	
	if($name){
		$location="uploads/$name";
		move_uploaded_file($tmp_name, $location);

		$query="INSERT INTO receipt(tenant,pay,name,path) VALUES((SELECT tenant_id FROM tenants WHERE First_Name='$firstname' AND Last_Name='$lastname'),(SELECT pay_id FROM payment WHERE pay_id='$pay_id' ),'$month_name','$location')";
		mysqli_query($conn,$query);

		$_SESSION['success']="Invoice Upload successful";
		header("Location: paymentTransaction.php");
	}else{
		array_push($errors, "Upload Failed");
	}

}*/

?>