<?php
// ---------- DATABASE CONNECTION ----------
$serverName = "AL_SHEEFRA\\SQLEXPRESS";
$database   = "alsheefradb";
$uid        = "Raheel";
$pass       = "Ltimcloud@2026";

$connectionInfo = ["Database"=>$database, "UID"=>$uid, "PWD"=>$pass];
$conn = sqlsrv_connect($serverName, $connectionInfo);
if(!$conn){ die("DB Connection Failed"); }

$popup = "";
$popupType = "error";
$forceLoginTab = false;

// ---------- SIGNUP ----------
if(isset($_POST["action"]) && $_POST["action"]==="signup"){
    $name  = trim($_POST["su_name"]);
    $phone = trim($_POST["su_phone"]);
    $email = trim($_POST["su_email"]);
    $p1    = trim($_POST["su_password"]);
    $p2    = trim($_POST["su_password2"]);

    if($p1 !== $p2){
        $popup       = "Passwords do not match!";
        $popupType   = "error";
        $forceLoginTab = false;
    } 
    else {

        // Check if email exists
        $check = sqlsrv_query($conn,"SELECT ASEmailID FROM ASAppUsers WHERE ASEmailID=?",[$email]);
        if($check && sqlsrv_has_rows($check)){
            $popup     = "Email already registered!";
            $popupType = "error";
            $forceLoginTab = false;
        } else {
            
            // INSERT user
            $query = "INSERT INTO ASAppUsers (ASUserName,ASPhoneNumber,ASEmailID,ASPassword,ASReEnterPassword)
                      VALUES (?,?,?,?,?)";

            $stmt = sqlsrv_query($conn,$query,[$name,$phone,$email,$p1,$p2]);

            if($stmt){
                $popup     = "Signup Successful! Please login now.";
                $popupType = "success";
                $forceLoginTab = true;  // Switch to login automatically
            } 
            else {
                $popup     = "Something went wrong while registering!";
                $popupType = "error";
            }
        }
    }
}

// ---------- LOGIN ----------
if(isset($_POST["action"]) && $_POST["action"]==="login"){
    $email = trim($_POST["li_email"]);
    $pass  = trim($_POST["li_password"]);

    $query = "SELECT * FROM ASAppUsers 
              WHERE LOWER(ASEmailID)=LOWER(?) AND ASPassword=?";
    $stmt  = sqlsrv_query($conn,$query,[$email,$pass]);
    $row   = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);

    if($row){
        header("Location: dashboard.php");
        exit;
    } else {
        $popup     = "Invalid Email or Password!";
        $popupType = "error";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Al Sheefra — Login / Signup</title>

<style>
/* ---- ROOT THEME ---- */
:root{
  --blue:#0066ff;
  --blue2:#0044c7;
  --milky:#f6faff;
  --white:#ffffff;
  --shadow:rgba(0,0,0,0.15);
  font-family:"Poppins",sans-serif;
}
*{box-sizing:border-box;margin:0;padding:0}

body{
  height:100vh;
  display:flex;align-items:center;justify-content:center;
  background:linear-gradient(135deg,#eaf1ff,#f9fbff);
  padding:20px;
}

/* Glass Card */
.card{
  width:100%;max-width:450px;
  background:#ffffffd9;
  border-radius:22px;
  padding:35px 33px;
  backdrop-filter:blur(14px);
  border:1px solid rgba(0,90,255,0.15);
  box-shadow:0 12px 35px var(--shadow);
  animation:fade .4s ease-out;
}
@keyframes fade{from{opacity:0;transform:translateY(15px);}to{opacity:1;}}

.logo{
  font-size:34px;font-weight:800;text-align:center;
  background:linear-gradient(135deg,#0047c2,#0099ff);
  -webkit-text-fill-color:transparent;
  -webkit-background-clip:text;
  margin-bottom:25px;
}

/* Tabs */
.toggle{
  display:flex;background:#e4edff;padding:6px;border-radius:18px;
  margin-bottom:25px;
}
.toggle button{
  flex:1;border:0;background:transparent;
  padding:12px;border-radius:14px;font-weight:600;
  cursor:pointer;color:#42516a;transition:.25s;
}
.toggle button.active{
  background:#0066ff;color:white;
  box-shadow:0 4px 12px rgba(0,102,255,0.4);
}

/* Inputs */
.field{position:relative;margin-bottom:22px;}
.field input{
  width:100%;padding:16px 14px 10px;
  border-radius:12px;border:1px solid #cddfff;
  background:#f3f7ff;font-size:15px;
}
.field label{
  position:absolute;left:14px;top:50%;transform:translateY(-50%);
  pointer-events:none;color:#6a7b95;
  transition:.25s;background:#f3f7ff;padding:0 6px;
}
.field input:focus,
.field input:not(:placeholder-shown){
  border-color:#0066ff;
}
.field input:focus + label,
.field input:not(:placeholder-shown) + label{
  top:-9px;font-size:12px;color:#0066ff;
}

/* Eye */
.eye{
  position:absolute;right:15px;top:50%;transform:translateY(-50%);
  cursor:pointer;font-size:18px;
}

/* Buttons */
.button{
  width:100%;padding:14px;border:0;border-radius:14px;
  background:linear-gradient(135deg,#4d9aff,#237dff); /* lighter */
  color:#fff;font-size:16px;margin-top:5px;cursor:pointer;font-weight:600;
  transition:0.25s;
  box-shadow:0 6px 18px rgba(50,120,255,0.28); /* lighter blur shadow */
}

.button:hover{
  transform:translateY(-2px);
  box-shadow:0 8px 22px rgba(138, 178, 252, 0.91); /* smoother hover blur */
}


/* Links */
.muted{text-align:center;margin-top:14px;font-size:14px;color:#44566f;}
.muted a{color:#0066ff;text-decoration:none;font-weight:600;}

.hidden{display:none}

/* Dialog */
.dialog-overlay{
  position:fixed;inset:0;
  background:rgba(0,40,120,0.25);
  display:none;align-items:center;justify-content:center;
  backdrop-filter:blur(8px);z-index:9999;
}
.dialog-card{
  width:320px;background:#ffffffef;padding:25px;
  border-radius:20px;text-align:center;
  border:1px solid rgba(0,60,255,0.18);
  box-shadow:0 8px 28px rgba(0,0,0,0.22);
  animation:pop .25s ease-out;
}
.dialog-card.success{border-left:8px solid #00b341;}
.dialog-card.error{border-left:8px solid #ff2f2f;}
@keyframes pop{from{transform:scale(.8);opacity:0;}to{transform:scale(1);opacity:1;}}
.dialog-btn{
  margin-top:18px;padding:12px 30px;border:0;border-radius:12px;
  background:linear-gradient(135deg,#0077ff,#004ed1);color:white;
  cursor:pointer;font-size:15px;
}
</style>
</head>

<body>

<!-- Dialog Box -->
<div id="dialogBox" class="dialog-overlay" style="<?php echo $popup ? 'display:flex;' : ''; ?>">
  <div class="dialog-card <?php echo $popupType; ?>">
    <h3><?php echo ucfirst($popupType); ?></h3>
    <p><?php echo $popup; ?></p>
    <button class="dialog-btn" onclick="closeDialog()">OK</button>
  </div>
</div>

<main class="card">

  <div class="logo">Al Sheefra</div>

  <div class="toggle">
    <button id="tabLogin" class="active">Login</button>
    <button id="tabSignup">Sign Up</button>
  </div>
  

  <!-- LOGIN -->
  <form method="post" id="loginPanel">
    <input type="hidden" name="action" value="login">

    <div class="field">
      <input type="email" name="li_email" placeholder=" " required>
      <label>Email</label>
    </div>

    <div class="field">
      <input type="password" name="li_password" placeholder=" " required id="loginPass">
      <label>Password</label>
      <span class="eye" onclick="togglePass('loginPass')">👁</span>
    </div>

    <button class="button">Login</button>
    <p class="muted">New user? <a href="#" id="toSignup">Create account</a></p>
  </form>

  <!-- SIGNUP -->
  <form method="post" id="signupPanel" class="hidden">
    <input type="hidden" name="action" value="signup">

    <div class="field">
      <input type="text" name="su_name" placeholder=" " required>
      <label>Full Name</label>
    </div>

    <div class="field">
      <input type="tel" name="su_phone" placeholder=" " required>
      <label>Phone Number</label>
    </div>

    <div class="field">
      <input type="email" name="su_email" placeholder=" " required>
      <label>Email</label>
    </div>

    <div class="field">
      <input type="password" name="su_password" placeholder=" " required id="sp1">
      <label>Password</label>
      <span class="eye" onclick="togglePass('sp1')">👁</span>
    </div>

    <div class="field">
      <input type="password" name="su_password2" placeholder=" " required id="sp2">
      <label>Confirm Password</label>
      <span class="eye" onclick="togglePass('sp2')">👁</span>
    </div>

    <button class="button">Sign Up</button>
    <p class="muted">Already registered? <a href="#" id="toLogin">Login</a></p>
  </form>

</main>

<script>
function closeDialog(){
    document.getElementById("dialogBox").style.display="none";
}

// Elements
const tL=document.getElementById("tabLogin");
const tS=document.getElementById("tabSignup");
const lp=document.getElementById("loginPanel");
const sp=document.getElementById("signupPanel");

function showLogin(){
  tL.classList.add("active");
  tS.classList.remove("active");
  lp.classList.remove("hidden");
  sp.classList.add("hidden");
}
function showSignup(){
  tS.classList.add("active");
  tL.classList.remove("active");
  sp.classList.remove("hidden");
  lp.classList.add("hidden");
}

document.getElementById("toSignup").onclick=e=>{e.preventDefault();showSignup();}
document.getElementById("toLogin").onclick=e=>{e.preventDefault();showLogin();}
tL.onclick=()=>showLogin();
tS.onclick=()=>showSignup();

function togglePass(id){
  let input = document.getElementById(id);
  input.type = input.type==="password" ? "text" : "password";
}

// AUTO SWITCH TO LOGIN AFTER SUCCESSFUL SIGNUP
<?php if($forceLoginTab){ ?>
    showLogin();
<?php } ?>
</script>

</body>
</html>
