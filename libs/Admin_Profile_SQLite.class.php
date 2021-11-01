<?php

class Admin_Profile_SQLite extends Admin_Profile {
   protected $rawProfileData;
   protected $db;

   public function __construct(&$db) {
      $this->db = $db;
      parent::__construct();
   }

   // Create a new profile entry
   // @return int profileId
   protected function create($user) {
      $this->db->q("
         insert into profiles (
             regDate, firstname, surname, email
         ) values (
            current_timestamp,
            '", $user->getParam("firstname"),"',
            '", $user->getParam("surname"), "',
            '", $user->getParam("mail"),"'
         )
      ");
      $profileId = $this->db->insert_id();
      $this->createAuthentication($user->getAuthModule(), $user->getExternalReference(), $profileId);
      return $profileId;
   }

   // Check if profile exists or not
   // @param int profile number
   // @return boolean
   public function idExists($profileId) {
      $this->db->q("select profileId as c from profiles where profileId='", $profileId, "'");
      if ($this->db->num_rows() == 1) {
         return true;
      }
      else {
         return false;
      }
   }

   // Get the profile number by suppling authentication module name and its reference ID.
   // @return int profile ID or -1 if not found.
   public function getIdByCrossref($module, $ref) {
      $this->db->q("
         select
            ProfileID
         from authentication
         where
            AuthModule = '", $module, "'
            and ExternalReference = '", $ref, "'
            and Erased is null
      ");

      if ($this->db->num_rows() == 0) {
         return -1;
      }
      else {
         $data = $this->db->fetch_assoc();
         return (int) $data['ProfileID'];
      }
   }

   // Sets profile data from database, by given profile ID.
   // @return void
   public function setById($profileId) {
      $data = $this->getRawProfileData($profileId);
      $this->profileId = $data->profileId;
      $this->firstname = $data->firstname;
      $this->surname = $data->surname;
      $this->email = $data->email;
      $this->active = $data->active;
      $this->lastLogin = $data->lastLogin;
   }

   // Update a profile with its user details. This is to keep profile in sync with
   // authententication engine. An example is when a person's details change in
   // Active Directory, we want to keep the profile details to match that.
   // Should be called after login, by the authentication module.
   // @return boolean
   public function setFromUser($user) {
      $profileId = $this->profileId;
      $active = ($user->getParam("active") ? "Y" : "N");
      $this->db->q("
         update profiles set
            firstname = '", $user->getParam("firstname"), "',
            surname = '", $user->getParam("surname"), "',
            email = '", $user->getParam("mail"), "',
            active = '", $active, "'
         where profileId = '", $profileId, "'
      ");
      $this->setById($profileId);
   }

   // Create authentication record in db.
   // @return int authentication ID
   protected function createAuthentication($module, $reference, $profileId) {
      $this->db->q("
         insert into authentication (
            AuthModule, ExternalReference, ProfileID, Created
         ) values (
            '", $module, "',
            '", $reference, "',
            '", $profileId, "',
            current_timestamp
         )
      ");
      return $this->db->insert_id();
   }

   // Get profile data
   // @return stdClass with profile details
   protected function getRawProfileData($profileId) {
      $this->db->q("
         select
            profileId,
            active,
            regDate,
            lastLogin,
            firstname,
            surname,
            email
         from profiles
         where
            profileId = '", $profileId, "'
            and erased is null
      ");

      if ($this->db->num_rows() == 0) {
         Log::d($this->db->error());
         return false;
      }
      else {
         return $this->db->fetch_object();
      }
   }

}

?>
