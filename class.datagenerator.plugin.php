<?php if (!defined('APPLICATION')) exit();
/*	Copyright 2015 Zachary Doll
*	This program is free software: you can redistribute it and/or modify
*	it under the terms of the GNU General Public License as published by
*	the Free Software Foundation, either version 3 of the License, or
*	(at your option) any later version.
*
*	This program is distributed in the hope that it will be useful,
*	but WITHOUT ANY WARRANTY; without even the implied warranty of
*	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*	GNU General Public License for more details.
*
*	You should have received a copy of the GNU General Public License
*	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
$PluginInfo['DataGenerator'] = array(
  'Name' => 'Data Generator',
  'Description' => 'Generates data for Vanilla. An evolution of Example Data by @R_J.',
  'Version' => '0.1',
  'RequiredApplications' => array('Vanilla' => '2.1.8'),
  'RequiredTheme' => FALSE,
  'RequiredPlugins' => FALSE,
  'MobileFriendly' => FALSE,
  'HasLocale' => FALSE,
  'RegisterPermissions' => FALSE,
  'SettingsUrl' => 'plugin/datagenerator',
  'SettingsPermission' => 'Garden.Settings.Manage',
  'Author' => 'Zachary Doll',
  'AuthorEmail' => 'hgtonight@daklutz.com',
  'AuthorUrl' => 'http://www.daklutz.com',
  'License' => 'GPLv3'
);

class DataGeneratorPlugin extends Gdn_Plugin {
  private $Users = NULL;
  
  public function PluginController_DataGenerator_Create($Sender, $Args = array()) {
    $Sender->Permission('Garden.Settings.Manage');
    $Sender->Title(T('Dummy Data Generator'));
    $Sender->AddSideMenu('plugin/datagenerator');
    $this->Dispatch($Sender, $Args);
  }

  public function Controller_Index($Sender) {
		$Sender->AddJsFile($this->GetResource('js/datagen.js', FALSE, FALSE));
		$Sender->AddCssFile($this->GetResource('design/datagen.css', FALSE, FALSE));
    $ConfigModule = new ConfigurationModule($Sender);

    $ConfigModule->Initialize(array(
      'DataGenerator.Lang' => array(
        'LabelCode' => 'Language',
        'Control'   => 'Dropdown',
        'Items'     => UserGenerator::$Langs
      )
    ));
    $Sender->ConfigurationModule = $ConfigModule;

    $this->Render('settings');
  }

  public function Controller_Users($Sender) {
    $NewUserCount = $this->GetCount($Sender);
    
    $Generator = new UserGenerator(C('DataGenerator.Lang'));
    for($i = 0; $i < $NewUserCount; $i++) {
      Gdn::UserModel()->InsertForBasic($Generator->Get(), FALSE, array('ValidateEmail' => FALSE, 'ValidateSpam' => FALSE, 'NoConfirmEmail' => TRUE));
    }
    $Sender->InformMessage(sprintf('%s users inserted.', $NewUserCount));
    
    $this->HandleDeliveryType($Sender);
  }

  public function Controller_Discussions($Sender) {
    $CategoryIDs = $this->GetCategories();
    
    $Generator = new TextGenerator(C('DataGenerator.Lang'));
    $DiscussionModel = new DiscussionModel();
    $InsertCount = $this->GetCount($Sender);

    for ($DiscussionCount = 0; $DiscussionCount < $InsertCount; $DiscussionCount++) {
      $UserID = $this->GetRandomUserID();
      
      $Discussion = array(
          'TransientKey' => Gdn::UserModel()->SetTransientKey($UserID),
          'DraftID' => '0',
          'CategoryID' => $CategoryIDs[array_rand($CategoryIDs)],
          'Name' => $Generator->Get(2, 10),
          'Body' => $this->GenerateBody($Generator),
          'InsertUserID' => $UserID
      );

      $DiscussionModel->Save($Discussion);
    }
    $Sender->InformMessage(sprintf('%s discussions inserted.', $DiscussionCount));
    
    $this->HandleDeliveryType($Sender);
  }

  public function Controller_Comments($Sender) {
    $NewComments = $this->GetCount($Sender);
    
    $Generator = new TextGenerator(C('DataGenerator.Lang'));
    $DiscussionModel = new DiscussionModel();
    $CommentModel = new CommentModel();
    $Discussions = $DiscussionModel->Get(0, 30)->ResultArray();

    for ($CommentCount = 0; $CommentCount < $NewComments; $CommentCount++) {
      $UserID = $this->GetRandomUserID();
      $DiscussionID = $Discussions[array_rand($Discussions)]['DiscussionID'];
      
      $Comment = array(
          'TransientKey' => Gdn::UserModel()->SetTransientKey($UserID),
          'DraftID' => '0',
          'DiscussionID' => $DiscussionID,
          'Body' => $this->GenerateBody($Generator),
          'InsertUserID' => $UserID
      );

      $CommentModel->Save($Comment);
    }
    $Sender->InformMessage(sprintf('%s comments inserted.', $CommentCount));
    
    $this->HandleDeliveryType($Sender);
  }

  private function HandleDeliveryType($Sender) {
    if($Sender->DeliveryType() === DELIVERY_TYPE_ALL) {
      Redirect('/plugin/datagenerator');
    }
    else {
      $Sender->Json('Result', TRUE);
      $Sender->Render('blank', 'utility', 'dashboard');
    }
  }
  
  private function GetUsers() {
    if(is_null($this->Users)) {
      $UserModel = new UserModel();
      $SystemUserID = $UserModel->GetSystemUserID();
      $Users = $UserModel->Get()->ResultArray();
      $UserIDs = array();
      foreach ($Users as $User) {
        $UserID = $User['UserID'];
        if ($UserID != $SystemUserID) {
          $UserIDs[$UserID] = $User['Name'];
        }
      }
      
      $this->Users = $UserIDs;
    }
    
    return $this->Users;
  }
  
  private function GetRandomUserID() {
    $Users = $this->GetUsers();
    return array_rand($Users);
  }
  
  private function GetRandomUserName() {
    $Users = $this->GetUsers();
    return $Users[array_rand($Users)];
  }
  
  private function GetCategories() {
    $CategoryModel = new CategoryModel();
    $Categories = $CategoryModel->Get()->ResultArray();
    $CategoryIDs = array();
    foreach ($Categories as $Category) {
      if ($Category['CategoryID'] > 0 && $Category['AllowDiscussions'] == 1) {
        $CategoryIDs[] = $Category['CategoryID'];
      }
    }
    
    return $CategoryIDs;
  }
  
  private function GetCount($Sender) {
    $Value = val(1, $Sender->RequestArgs, 5);
    
    if(!is_numeric($Value)) {
      $Value = 5;
    }
    return $Value;
  }
  
  private function GenerateBody($Generator) {
    $Body = '';
    $GraphCount = rand(1, 6);
    for ($i = 0; $i < $GraphCount; $i++) {
      if(rand(0, 100) < 10) {
        $Body .= '@' . $this->GetRandomUserName() . "\r\n\r\n";
      }
      else {
        $Body .= $Generator->Get(1, 100) . "\r\n\r\n";
      }
    }
    
    return $Body;
  }
  
  public function Base_GetAppSettingsMenuItems_Handler($Sender) {
    $Menu = &$Sender->EventArguments['SideMenu'];
    $Menu->AddLink('Add-ons', T('Generate Random Data'), 'plugin/datagenerator', 'Garden.Settings.Manage');
  }
  
  public function Setup() {
    if(C('DataGenerator.Lang', FALSE) === FALSE) {
      SaveToConfig('DataGenerator.Lang', 'en');
    }
  }
}
