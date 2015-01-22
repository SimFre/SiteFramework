<?php


// Take a look at legacy profile.class.php and avatar.class.php.

abstract class Admin_Profile {
   protected $profileId = 0;
   protected $firstname;
   protected $surname;
   protected $email;
   protected $active = false;
   protected $lastLogin;

   // Included construct doesn't do anything for this version,
   // but allows for future expansion. Extended classes should
   // call parent::__construct().
   public function __construct() {
   }

   public function __toString() {
      return "".$this->profileId;
   }

   // Create a new profile entry
   // @return int profileId
   abstract protected function create($user);

   // Check if profile exists or not
   // @param int profile number
   // @return boolean
   abstract public function idExists($profileId);

   // Get a new profile object by suppling a profile ID number.
   // @param int profile number
   // @return Admin_Profile object.
   public function getById($profileId) {
      if ($this->idExists($profileId)) {
         $p = clone $this;
         $p->setById($profileId);
         return $p;
      }
      else {
         return false;
      }
   }

   // Get profile by sending in an Admin_User object.
   // @param Admin_User object
   // @param boolean
   // @return Admin_Profile object.
   public function getByUser($user, $createIfMissing = false) {
      $profileId = $this->getIdByCrossref(
         $user->getAuthModule(),
         $user->getExternalReference()
      );

      if ($profileId < 0 && $createIfMissing == true) {
         $profileId = $this->create($user);
      }

      if ($profileId >= 0) {
         return $this->getById($profileId);
      }
      else {
         return false;
      }
   }

   // Get the profile number by suppling authentication module name and its reference ID.
   // @return int profile ID or -1 if not found.
   abstract public function getIdByCrossref($module, $ref);

   // Sets profile data from database, by given profile ID.
   // @return void
   abstract public function setById($profileId);

   // Update a profile with its user details. This is to keep profile in sync with
   // authententication engine. An example is when a person's details change in
   // Active Directory, we want to keep the profile details to match that.
   // Should be called after login, by the authentication module.
   // @return boolean
   abstract public function setFromUser($user);

   //protected function initializeFirstname() {
   //   return $this->user->getFirstname();
   //}

   //protected function initializeSurname() {
   //   return $this->user->getSurname();
   //}

   //protected function initializeEmail() {
   //   return $this->user->initializeEmail();
   //}

   //protected function initializeActive() {
   //   return $this->user->isActive();
   //}

   //protected function initializeLastLogin() {
   //   return date("Y-m-d H:i:s");
   //}

   //protected function initializeProfileId() {
   //   return 0;
   //}

   //public function getAuthId() {
   //   return $this->authId;
   //}

   //public function getAuthModule() {
   //   return $this->user->moduleName();
   //}

   //public function getExternalReference() {
   //   return $this->user->getExternalReference();
   //}

   public function getId() {
      return $this->profileId;
   }

   public function getFirstname() {
      return $this->firstname;
   }

   public function getSurname() {
      return $this->surname;
   }

   public function getEmail() {
      return $this->email;
   }

   public function isActive() {
      return $this->active;
   }

   public function getLastLogin() {
      return $this->lastLogin;
   }

}

?>
