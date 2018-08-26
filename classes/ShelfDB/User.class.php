<?php

namespace ShelfDB {
  class User {

    private $db         = null;
    private $isLoggedIn = false;

    private const hashAlgorithm = PASSWORD_BCRYPT;
    private const hashOptions   = array();

    /** Constructor */
    function __construct($dbobj) {
      $this->db = $dbobj;
    }

    private function db() : \ShelfDB {
      return $this->db;
    }

    public function ResumeSession() {
      // Obtain session ID
      if( session_start() ) {
        // Check for corrent user
        if( isset( $_SESSION['password'] ) && isset( $_SESSION['username'] ) && isset( $_SESSION['userid'] )
          && $this->CheckHashedLogin( $_SESSION['userid'], $_SESSION['username'], $_SESSION['password']) ) {
          // All good
          \Log::Debug("Successfully logged in user \"".$_SESSION['username']."\"");
          $this->isLoggedIn = true;
          return true;
        } else {
          // Incorrect, start new session
          $this->LogOut();

          \Log::Debug("Incorrect session user or password, restarted session");
          $this->isLoggedIn = false;
          return false;
        }
      }
    }

    public function LogOut() {
      session_unset();
      session_destroy();
      session_write_close();
      setcookie(session_name(),'',0,'/');
      session_start();
      session_regenerate_id(true);
    }

    public function GetById($id) {

      if( !$id ) {
        return null;
      }

      $query = "SELECT * FROM users WHERE id = $id;";

      $res = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);
      if( !$res ) return false;

      $user = $res->fetch_assoc();
      $res->free();

      return $user;
    }

    public function GetByName($username) {
      $username = $this->db()->sql->real_escape_string(strtolower($username));
      $query = "SELECT * FROM users WHERE name = '$username';";

      $res = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);
      if( !$res ) return false;

      $user = $res->fetch_assoc();
      $res->free();

      return $user;
    }

    public function GetAllByGroupId($id) {
      $query =
        "SELECT u.id, u.name, u.email, u.isadmin FROM users_groups ug "
        //."LEFT JOIN groups g ON g.id = ug.groupid "
        ."LEFT JOIN users u ON u.id = ug.userid "
        ."WHERE ug.groupid IN (".join(",",$id).")";

      $res = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);

      if( $res ) {
        $data = $res->fetch_all(MYSQLI_ASSOC);
        $res->free();

        if( $data && !is_array($data[0]) ) {
          $data = array($data);
        }

        return $data;
      } else {
        return null;
      }
    }

    public function GetLoggedInUserId() {
      if( isset( $_SESSION['userid'] ) ) {
        return $_SESSION['userid'];
      } else {
        return null;
      }
    }

    public function GetLoggedInUser() {
      return $this->GetById($this->GetLoggedInUserId());
    }

    public function SendRegistrationMailById($id) {
      $user = $this->GetById($id);

      if( $user ) {
        $to = $user['email'];
        ini_set("SMTP", "aspmx.l.google.com");
        ini_set("sendmail_from", "noreply@localhost");
        mail( $to, "TESTMAIL [ShelfDB] Registration confirmation link", "This is a test.", "X-Mailer: PHP/" . phpversion() );
      }
    }

    public function GetUserIdByName($username) {
      $username = $this->db()->sql->real_escape_string($username);
      $query = "SELECT id FROM users WHERE name = '$username'";
      $res = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);

      $data = $res->fetch_assoc();

      $res->free();

      if( $data )
        return $data['id'];
      else
        return 0;
    }

    public function GetUserPasswordHashById(int $id) {

      $query = "SELECT passhash FROM users WHERE id = $id";
      $res = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);

      $data = $res->fetch_assoc();

      $res->free();

      return $data['passhash'];
    }

    public function SetUserPasswordHashById(int $id, $pwHash) {
      $pwHash   = $this->db()->sql->real_escape_string($pwHash);
      $query = "UPDATE users SET passhash = '$pwHash' WHERE id = $id;";
      $res = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);

      return $res;
    }

    public function LoginUser( $username, $password ) {
      if( $user = $this->CheckLogin( $username, $password ) ) {
        $_SESSION['username'] = $user['name'];
        $_SESSION['password'] = $user['passhash'];
        $_SESSION['userid']   = $user['id'];
        session_write_close();

        $this->db()->History()->Add($user['id'],'U','login','none','','');

        return $this->ResumeSession();
      }
      return false;
    }

    public function CheckHashedLogin(int $userid, $username, $pwHash) {

      // TODO LDAP
      $user   = $this->GetById( $userid );

      if( $user ) {
        $dbHash = $user['passhash'];

        if( strcmp($pwHash, $dbHash) == 0
          && strtolower($username) == strtolower($user['name']) ) {
          return true;
        } else {
          return false;
        }
      } else {
        return false;
      }
    }

    public function HashPassword($password) {
      $hash = password_hash( $password, User::hashAlgorithm, User::hashOptions );
    }

    public function CheckLogin($username, $password) {
      $user   = $this->GetByName($username);

      if( !$user ) return false;

      if( password_verify($password, $user['passhash']) ) {

        // Rehash if necessary
        if( password_needs_rehash( $user['passhash'], User::hashAlgorithm, User::hashOptions  ) ) {
          $newHash = $this->HashPassword($password);
          $this->SetUserPasswordHashById( $user['id'], $newHash);
          $user['passhash'] = $newHash;
        }
        return $user;

      } else {
        return false;
      }
    }

    public function IsLoggedIn() {
      return $this->isLoggedIn;
    }

    public function IsAdmin() {
      if( $this->IsLoggedIn() ) {
        $id    = $this->GetLoggedInUserId();
        $query = "SELECT isadmin FROM users WHERE id = $id;";
        $res   = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);

        if( !$res ) return false;

        $data  = $res->fetch_assoc();

        $res->free();

        if( intVal($data['isadmin']) == 1 )
          return true;
      }

      return false;
    }
  }
}

?>
