<?php
/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';

class eebsm extends eqLogic {
  
  public static function cron15() {
  	foreach (eqLogic::byType('eebsm', true) as $eqLogic) {
      if ($eqLogic->getConfiguration('ping') == "1"){
        try {
          $exec_string = 'sudo ping -c 1 -w 1 ' . $eqLogic->getConfiguration('adresseip') ;
          exec($exec_string, $output, $return);
          $output = array_values(array_filter($output));
          $latency = "-1";
          if (!empty($output[1])) {
            if (count($output) >= 5) {				
              $response = preg_match("/time(?:=|<)(?<time>[\.0-9]+)(?:|\s)ms/", $output[count($output)-4], $matches);
                  if ($response > 0 && isset($matches['time'])) {
                      $latency = $matches['time'];
                  }				
              }	
          }

          if ($latency == -1){
            log::add(__CLASS__, 'debug', $eqLogic->getHumanName(). ", " .$eqLogic->getConfiguration('adresseip'). ": Ping NOK");
            $name = "Statut";
    		$info = $eqLogic->getCmd(null, $name);
    		$eqLogic = $info->getEqLogic();
            $eqLogic->checkAndUpdateCmd($name, "0");   
          }else{          
            log::add(__CLASS__, 'debug', $eqLogic->getHumanName(). ", " .$eqLogic->getConfiguration('adresseip'). ": Ping OK, Latance: ".$latency);
            $eqLogic->refresh();
            
          }

        } catch (Exception $exc) {
          log::add(__CLASS__, 'error', "Expression cron non valide pour ping: ". $eqLogic->getHumanName());					
        }
      }      
	}
  }
  
  public function preUpdate() {  
    $retour_module = $this->GetModule();    
    if ($retour_module != "Erreur" && $retour_module != ""){          
      $this->setConfiguration('module',$retour_module);
    }else{
      throw new Exception(__("Oups! N'y aurait-il pas un petit problème dans la saisie des informations?", __FILE__));
      die();
    }
  }

  public function postUpdate() {
    if ($this->getConfiguration('adresseip') == '') {
      throw new Exception(__("L'adresse IP ne peut pas être vide", __FILE__));
    }else{
      	if ($this->getConfiguration('password') == '') {
      		throw new Exception(__("Le mot de passe ne peut pas être vide", __FILE__));
    	}else{
          	//$status->setOrder(1);
          	
          	$json_index = $this->GetIndex();
            if ($json_index == 'Erreur'){
                throw new Exception(__("Oups! N'y aurait-il pas un petit problème dans la saisie des informations?", __FILE__));
                die();
            }
          
          	$json_count = count($json_index);
          	if ($json_count == 0){
              	throw new Exception(__("Oups! N'y aurait-il pas un petit problème dans la saisie des informations?", __FILE__));
                die();
            }
          
          	$ordre = 0;
          
          
          	$refresh = $this->getCmd(null, 'refresh');
            if (!is_object($refresh)) {
                $refresh = new eebsmCmd();
                $refresh->setName(__('Rafraichir', __FILE__));
              	$refresh->setEqLogic_id($this->getId());
                $refresh->setLogicalId('refresh');
                $refresh->setType('action');
                $refresh->setSubType('other');
              	$refresh->setIsVisible(0);
                $refresh->setConfiguration('visible_cmd',"0");
                $refresh->setConfiguration('commande',"-1");
            }
          	$refresh->setOrder($ordre);
          	$ordre++;
            $refresh->save();
          
          	$name = "Redémarrer";            
            $info = $this->getCmd(null, $name);
            if (!is_object($info)) {
              log::add(__CLASS__, 'debug', "Ajout du bouton de redémarrage");
              $info = new eebsmCmd();
              $info->setName(__($name, __FILE__));
              $info->setLogicalId($name);
              $info->setEqLogic_id($this->getId());
              if($this->getConfiguration('widgets') == '1') $info->setTemplate('dashboard', 'eebsm_button');            
              if($this->getConfiguration('widgets') == '1') $info->setTemplate('mobile', 'eebsm_button');            
              $info->setType('action');
              $info->setSubType('other');
              $info->setConfiguration('type','reboot');
              $info->setIsVisible(0);
              $info->setConfiguration('visible_cmd',"1");
              $info->setConfiguration('commande',"-1");
            }
          	$info->setOrder($ordre);
          	$ordre++;
            $info->save();
          
          	$name = "Statut";            
            $info = $this->getCmd(null, $name);
            if (!is_object($info)) {
              log::add(__CLASS__, 'debug', "Ajout du statut");
              $info = new eebsmCmd();
              $info->setName(__($name, __FILE__));
              $info->setLogicalId($name);
              $info->setEqLogic_id($this->getId());
              $info->setTemplate('dashboard', 'eebsm_status');
        	  $info->setTemplate('mobile', 'eebsm_status');
        	  $info->setType('info');
              $info->setSubType('binary');              
              $info->setConfiguration('type','status');
              $info->setIsVisible(1);
              $info->setConfiguration('visible_cmd',"1");
              $info->setConfiguration('commande',"-1");                
            }            
            $info->setOrder($ordre);
          	$ordre++;
          	$info->save();            
            /*$eqLogic = $info->getEqLogic();      		
            $eqLogic->checkAndUpdateCmd($name, "1");*/

           
          	
            for ($i = 0; $i < $json_count; $i++) {
              
                if ($json_index[$i]['type'] == 'info'){					
                    $cmd_id = $json_index[$i]['id'];
                    $name = $json_index[$i]['nom'];
                    $value = $json_index[$i]['value'];
                    $unite = $json_index[$i]['unit'];
                  	log::add(__CLASS__, 'debug', "Ajout d'une info: " . $name);
                  
                    $info = $this->getCmd(null, $name);
                  	if (!is_object($info)) {
                        $info = new eebsmCmd();
                        $info->setName(__($name, __FILE__));
                      	$info->setLogicalId($name);
                        $info->setEqLogic_id($this->getId());
                        if($this->getConfiguration('widgets') == '1') $info->setTemplate('dashboard', 'core::tile');
                        if($this->getConfiguration('widgets') == '1') $info->setTemplate('mobile', 'core::tile');
                        $info->setType('info');
                        $info->setSubType('string');
                        $info->setConfiguration('type','info');                        
                        $info->setConfiguration('visible_cmd',"1");
                    }     
                  	$info->setOrder($ordre);
          			$ordre++;
          			$info->setConfiguration('commande',$cmd_id);
                    $info->setUnite($unite);                  
                    $info->save();                    

                }else if ($json_index[$i]['type'] == 'toogle'){
                    $name = $json_index[$i]['nom'];          
                    $state = $json_index[$i]['state'];
                    $id_0 = $json_index[$i]['id0'];
                    $id_1 = $json_index[$i]['id1'];
                  	log::add(__CLASS__, 'debug', "Ajout d'un toggle: " . $name);
					
                    $info = $this->getCmd(null, $name);
                    if (!is_object($info)) {
                        $info = new eebsmCmd();
                        $info->setName(__($name, __FILE__));
                      	$info->setLogicalId($name);
                        $info->setEqLogic_id($this->getId());
                        $info->setType('info');
                        $info->setConfiguration('visible_cmd',"0");
                        $info->setSubType('binary');
                        $info->setIsVisible(0);
                    }                    
                    $info->setOrder($ordre);
          			$ordre++;
          			$info->setConfiguration('commande',$id_0);
                    $info->save();                    

                    $info_id = $info->getId();
                    $name_0 = "Off_" . $name;
                    $cmd_0 = $this->getCmd(null, $name_0);
                    if (!is_object($cmd_0)) {
                        $cmd_0 = new eebsmCmd();
                        $cmd_0->setName(__($name_0, __FILE__));
                      	$cmd_0->setLogicalId($name_0);
                        $cmd_0->setEqLogic_id($this->getId());
                        if($this->getConfiguration('widgets') == '1') $cmd_0->setTemplate('dashboard', 'eebsm_toggle');
                        if($this->getConfiguration('widgets') == '1') $cmd_0->setTemplate('mobile', 'eebsm_toggle');
                        $cmd_0->setType('action');
                        $cmd_0->setSubType('other');
                        $cmd_0->setConfiguration('type','toogle');
                        $cmd_0->setConfiguration('state','Off');                        
                        $cmd_0->setConfiguration('visible_cmd',"1");
                    } 
                  	$cmd_0->setOrder($ordre);
          			$ordre++;
          			$cmd_0->setvalue($info_id);          
                    $cmd_0->setConfiguration('parent',$name);
                    $cmd_0->setConfiguration('commande',$id_0);
                    $cmd_0->save();

                    $name_1 = "On_".$name;
                    $cmd_1 = $this->getCmd(null, $name_1);
                    if (!is_object($cmd_1)) {
                        $cmd_1 = new eebsmCmd();
                        $cmd_1->setName(__($name_1, __FILE__));
                      	$cmd_1->setLogicalId($name_1);
                        $cmd_1->setEqLogic_id($this->getId());
                        if($this->getConfiguration('widgets') == '1') $cmd_1->setTemplate('dashboard', 'eebsm_toggle');
                        if($this->getConfiguration('widgets') == '1') $cmd_1->setTemplate('mobile', 'eebsm_toggle');
                        $cmd_1->setType('action');
                        $cmd_1->setSubType('other');
                        $cmd_1->setConfiguration('type','toogle');
                        $cmd_1->setConfiguration('state','On');  
                        $cmd_1->setConfiguration('visible_cmd',"1");
                    }                    
                    $cmd_1->setOrder($ordre);
          			$ordre++;
          			$cmd_1->setvalue($info_id);          
                    $cmd_1->setConfiguration('parent',$name);
                    $cmd_1->setConfiguration('commande',$id_1);                        		 
                    $cmd_1->save();
					
                }else if ($json_index[$i]['type'] == 'range'){
                  	$id_cmd = $json_index[$i]['id'];
                    $name = $json_index[$i]['nom'];
                    $value = $json_index[$i]['value'];
                    $min = $json_index[$i]['min'];
                    $max = $json_index[$i]['max'];
                    log::add(__CLASS__, 'debug', "Ajout d'un curseur: " . $name);
                  
                  	$info = $this->getCmd(null, 'Value_'.$name);
                    if (!is_object($info)) {
                        $info = new eebsmCmd();
                        $info->setName(__('Value_'.$name, __FILE__));
                      	$info->setLogicalId('Value_'.$name);
                        $info->setEqLogic_id($this->getId());
                        $info->setType('info');
                        $info->setSubType('numeric');                        
                        $info->setIsVisible(0);
                        $info->setConfiguration('visible_cmd',"0");
                    } 
                    $info->setOrder($ordre);
          			$ordre++;
          			$info->setConfiguration('value',$value);
                    $info->setConfiguration('commande',$id_cmd);
                    $info->setConfiguration('minValue', $min);
                    $info->setConfiguration('maxValue', $max);
                    $info->save();                    
                  	
                    $info_id = $info->getId();                  	
                    $info = $this->getCmd(null, $name);
                  	if (!is_object($info)) {
                        $info = new eebsmCmd();
                        $info->setName(__($name, __FILE__));
                      	$info->setLogicalId($name);
                        $info->setEqLogic_id($this->getId());
                        if($this->getConfiguration('widgets') == '1') $info->setTemplate('dashboard', 'eebsm_slider');
                        if($this->getConfiguration('widgets') == '1') $info->setTemplate('mobile', 'eebsm_slider');
                        $info->setType('action');
                        $info->setSubType('slider');
                        $info->setConfiguration('type','range');
                        $info->setConfiguration('visible_cmd',"1");
                    }  
                    $info->setOrder($ordre);
          			$ordre++;
          			$info->setConfiguration('minValue', $min);
                    $info->setConfiguration('maxValue', $max);
                    $info->setConfiguration('commande',$id_cmd);
                    $info->setConfiguration('parent','Value_'.$name);
                    $info->setValue($info_id);                        
                    $info->save(); 
                  
                }else if ($json_index[$i]['type'] == 'list'){
                  
                  	$id_cmd = $json_index[$i]['id'];
                    $name = $json_index[$i]['nom'];
                    $namelist = $json_index[$i]['nomliste'];
                    $value = $json_index[$i]['value'];
                  	log::add(__CLASS__, 'debug', "Ajout d'un item de liste " . $name);
                  
                  	$info = $this->getCmd(null, 'Value_'.$namelist);
                    if (!is_object($info)) {
                        $info = new eebsmCmd();
                        $info->setName(__('Value_'.$namelist, __FILE__));
                        $info->setLogicalId('Value_'.$namelist);
                        $info->setEqLogic_id($this->getId());
                        $info->setType('info');
                        $info->setSubType('string');
                        $info->setIsVisible(0);
                        $info->setConfiguration('visible_cmd',"0");
                    }                    
                    $info->setOrder($ordre);
          			$ordre++;
          			$info->setConfiguration('commande',$id_cmd);
                    $info->save();                    
                  
                  	$info_id = $info->getId();
                  	
                    $info = $this->getCmd(null, $namelist);
                  	if (!is_object($info)) {
                        $info = new eebsmCmd();
                        $info->setName(__($namelist, __FILE__));
                        $info->setLogicalId($namelist);
                        $info->setEqLogic_id($this->getId());
                        $info->setType('action');
                        $info->setSubType('select');
                        $info->setConfiguration('type','list');
                        $info->setConfiguration('visible_cmd',"1");
                    }
					
                  	$work_list = "";
                  	for ($j = 0; $j < $json_count; $j++) {
                      if ($json_index[$j]['type'] == 'list'){                        
                        if($namelist == $json_index[$j]['nomliste']){
                          if ($work_list.$json_index[$j]['nom'] != ""){
                          	$work_list = $work_list.$json_index[$j]['id'].'|'.$json_index[$j]['nom'].';';
                          }
                        }                        
                      }
                  	}
                  	$info->setOrder($ordre);
          			$ordre++;
          			$final_list = substr($work_list, 0, -1);                    
                    $info->setConfiguration('listValue', $final_list);
                    $info->setConfiguration('commande',$id_cmd);
                  	$info->setConfiguration('list',$namelist);
                    $info->setConfiguration('parent','Value_'.$namelist);
                    $info->setConfiguration('state',$name);
                    $info->setValue($info_id);  
                    $info->save();
                  
                }else if ($json_index[$i]['type'] == 'bouton'){
                  	$id_cmd = $json_index[$i]['id'];
                    $name = $json_index[$i]['nom'];                    
                  	log::add(__CLASS__, 'debug', "Ajout d'un bouton " . $name); 
					
                  	$info = $this->getCmd(null, $name);
                    if (!is_object($info)) {
                        $info = new eebsmCmd();
                        $info->setName(__($name, __FILE__));
                      	$info->setLogicalId($name);
                        $info->setEqLogic_id($this->getId());
                        if($this->getConfiguration('widgets') == '1') $info->setTemplate('dashboard', 'eebsm_button');
                        if($this->getConfiguration('widgets') == '1') $info->setTemplate('mobile', 'eebsm_button');
                        $info->setType('action');
                        $info->setSubType('other');
                        $info->setConfiguration('type','bouton');
                    	$info->setConfiguration('visible_cmd',"1");
                    }                    
                    $info->setOrder($ordre);
          			$ordre++;
          			$info->setConfiguration('commande',$id_cmd);                  	
                    $info->save();
                  
                }else if ($json_index[$i]['type'] == 'color'){
                  	
                  	$id_cmd = $json_index[$i]['id'];
                    $name = $json_index[$i]['nom'];
                    $color_r = $json_index[$i]['r'];
                    $color_g = $json_index[$i]['g'];
                    $color_b = $json_index[$i]['b'];
                  	$hex_value= sprintf("#%02x%02x%02x", $color_r, $color_g, $color_b);
					log::add(__CLASS__, 'debug', "Ajout d'une couleur " . $name);                  	
                  
                  	$info = $this->getCmd(null, 'Value_'.$name);
                    if (!is_object($info)) {
                        $info = new eebsmCmd();
                        $info->setName(__('Value_'.$name, __FILE__));
                        $info->setLogicalId('Value_'.$name);
                        $info->setEqLogic_id($this->getId());
                        $info->setType('info');
                        $info->setSubType('string');
                        $info->setIsVisible(0);
                      	$info->setConfiguration('visible_cmd',"0");
                    }
                    
                    $info->setOrder($ordre);
          			$ordre++;
          			$info->setConfiguration('commande',$id_cmd);
                    $info->save();
                  
                  	$info_id = $info->getId();
                  
                  	$info = $this->getCmd(null, $name);
                    if (!is_object($info)) {
                        $info = new eebsmCmd();
                        $info->setName(__($name, __FILE__));
                      	$info->setLogicalId($name);
                        $info->setEqLogic_id($this->getId());
                        if($this->getConfiguration('widgets') == '1') $info->setTemplate('dashboard', 'eebsm_color');
                        if($this->getConfiguration('widgets') == '1') $info->setTemplate('mobile', 'eebsm_color');
                        $info->setType('action');
                        $info->setSubType('color');
                      	$info->setConfiguration('type','color');
                      	$info->setConfiguration('parameters', 'color=FFFFFF');
                    	$info->setConfiguration('visible_cmd',"1");
                    }
                    
                    $info->setOrder($ordre);
          			$ordre++;
          			$info->setConfiguration('commande',$id_cmd);
                  	$info->setConfiguration('parent','Value_'.$name);
                    $info->setConfiguration('state',$hex_value);
                    $info->setValue($info_id);                  	
                    $info->save();
                }
            }
          
          	//Mise a jour SPIFFS eebsm
          	$eebsm_plugin = eebsm::GetParam('Jeedom');
            log::add(__CLASS__, 'debug', 'Toggle Jeedom: '.$eebsm_plugin);
          	
            $eebsm_adresseip = eebsm::GetParam('Jeedom_IP');
            log::add(__CLASS__, 'debug', 'Adresse IP paramétrée: '.$eebsm_adresseip);
          
          	$eebsm_key = eebsm::GetParam('Jeedom_Key');
          	log::add(__CLASS__, 'debug', 'Clé Jeedom décryptée: '.$eebsm_key);
          
            $eebsm_refresh = eebsm::GetParam('Jeedom_Refresh');
            log::add(__CLASS__, 'debug', 'Refresh paramétré: '.$eebsm_refresh);
          
            $adresseip = network::getNetworkAccess();
          	$cleapi = jeedom::getApiKey();
            $idrefresh = $refresh->getId(); 
          
          	
          	$update = 0;
            if ($eebsm_plugin == "Null" || $eebsm_adresseip == "Erreur"){
              log::add(__CLASS__, 'debug', 'Création des paramètres SPIFFS');
              $result = eebsm::AddListParamBool('Jeedom',"1");
              log::add(__CLASS__, 'debug', 'Ajout du paramètre Jeedom: 1 : '.$result);

              if ($eebsm_adresseip == "Null" || $eebsm_adresseip == "Erreur" ){
                $result = eebsm::AddListParam('Jeedom_IP',$adresseip);
                log::add(__CLASS__, 'debug', 'Ajout du paramètre Jeedom_IP: '.$adresseip.' : '.$result);
              }

              if ($eebsm_key == "Null" || $eebsm_key == "Erreur"){
                $result = eebsm::AddListParamCrypted('Jeedom_Key',$cleapi);
                log::add(__CLASS__, 'debug', 'Ajout du paramètre Jeedom_Key crypté: '.$cleapi.' : '.$result);
              }

              if ($eebsm_key == "Null" || $eebsm_key == "Erreur"){
                $result = eebsm::AddListParam('Jeedom_Refresh',$idrefresh);
                log::add(__CLASS__, 'debug', 'Ajout du paramètre Jeedom_Refresh: '.$idrefresh.' : '.$result);
              }
              eebsm::CreateListParam();
              
            }else{
              if ($eebsm_plugin != 1){
                $result = eebsm::UpdateListParam('Jeedom',"1");
                log::add(__CLASS__, 'debug', 'Modification du paramètre Jeedom: 1 : '.$result);
              }
              if ($adresseip != $eebsm_adresseip){				
                $update++;
                $result = eebsm::UpdateListParam('Jeedom_IP',$adresseip);
                log::add(__CLASS__, 'debug', 'Modification du paramètre Jeedom_IP: '.$adresseip.' : '.$result);				              	
              }   
              if ($cleapi != $eebsm_key){
                $update++;
                $result = eebsm::UpdateListParam('Jeedom_Key',$cleapi);
                log::add(__CLASS__, 'debug', 'Modification du paramètre Jeedom_Key crypté: '.$cleapi.' : '.$result);
              }
              if ($idrefresh != $eebsm_refresh){				
                $update++;
                $result = eebsm::UpdateListParam('Jeedom_Refresh',$idrefresh);
                log::add(__CLASS__, 'debug', 'Modification du paramètre Jeedom_Refresh: '.$idrefresh.' : '.$result);							
              }
              if ($update>0) eebsm::SaveListParam();

            }
          	eebsm::refresh();
        }
    }
  }

  

  
  public function preRemove() {
  }
  
  public function postRemove() {
  }
  
  public function decrypt() {
	$this->setConfiguration('password', utils::decrypt($this->getConfiguration('password')));
	$this->setConfiguration('adresseip', utils::decrypt($this->getConfiguration('adresseip')));
  }

  public function encrypt() {
	$this->setConfiguration('password', utils::encrypt($this->getConfiguration('password')));
	$this->setConfiguration('adresseip', utils::encrypt($this->getConfiguration('adresseip')));
  }
  
  public function Reboot_Module() {    
    $url = 'http://'.$this->getConfiguration('adresseip').'/reboot?key='.$this->getConfiguration('password');
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        return "Erreur";
    }
    curl_close($ch);    
    log::add(__CLASS__, 'debug', 'Redémarrage du module: ' . $result);
    return $module;
  }
  
  
  
  
  
  public function UpdateListParam($param,$value) {
    $url = 'http://'.$this->getConfiguration('adresseip').'/updatelistparam?key='.$this->getConfiguration('password').'&param='.$param.'&value='.$value;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        return "Erreur";
		//throw new Exception(__(, __FILE__));        
    }
    curl_close($ch);
    return $result;    
  }
  
  
  
  public function SaveListParam() {
    $url = 'http://'.$this->getConfiguration('adresseip').'/savelistparam?key='.$this->getConfiguration('password');
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        return "Erreur";
		//throw new Exception(__(, __FILE__));        
    }
    curl_close($ch);
    return $result;    
  }
  
  public function AddListParam($param,$value) {
    $url = 'http://'.$this->getConfiguration('adresseip').'/addlistparam?key='.$this->getConfiguration('password').'&param='.$param.'&value='.$value.'&type=hidden-text';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        return "Erreur";
    }
    curl_close($ch);
    return $result;    
  }
  
  public function AddListParamCrypted($param,$value) {
    $url = 'http://'.$this->getConfiguration('adresseip').'/addlistparam?key='.$this->getConfiguration('password').'&param='.$param.'&value='.$value.'&type=crypted-text';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        return "Erreur";
    }
    curl_close($ch);
    return $result;    
  }
  
  public function AddListParamBool($param,$value) {
    $url = 'http://'.$this->getConfiguration('adresseip').'/addlistparam?key='.$this->getConfiguration('password').'&param='.$param.'&value='.$value.'&type=toogle';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        return "Erreur";
    }
    curl_close($ch);
    return $result;    
  }
  
  public function CreateListParam() {
    $url = 'http://'.$this->getConfiguration('adresseip').'/createlistparam?key='.$this->getConfiguration('password');
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        return "Erreur";
		//throw new Exception(__(, __FILE__));        
    }
    curl_close($ch);
    return $result;    
  }
  
  public function GetParam($param) {
    $url = 'http://'.$this->getConfiguration('adresseip').'/getparam?key='.$this->getConfiguration('password').'&value='.$param;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        return "Erreur";
    }
    curl_close($ch);
    return $result;    
  }
   
  
  public function GetModule() {    
    $url = 'http://'.$this->getConfiguration('adresseip').'/getmodule?key='.$this->getConfiguration('password');
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        return "Erreur";
    }
    curl_close($ch);
    $jsonData = json_decode($result, true);
    $module = $jsonData['module'][0]['nom'];
    log::add(__CLASS__, 'debug', 'GetModule: ' . $module);
    if ($module == '') $module = 'Non déclaré';
    return $module;
  }
  
  public function GetIndex() {
    $url = 'http://'.$this->getConfiguration('adresseip').'/getindex?key='.$this->getConfiguration('password');
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        return "Erreur";
    }
    curl_close($ch);
    $jsonData = json_decode($result, true);
    $table = $jsonData['actions'];
    return $table;
  }
  
  
  public function refresh() {
    
    
    $json_index = $this->GetIndex();
    $name = "Statut";
    $info = $this->getCmd(null, $name);
    $eqLogic = $info->getEqLogic();
    log::add(__CLASS__, 'debug', 'Refresh module: '.$eqLogic->getHumanName()." : ".$eqLogic->getConfiguration('adresseip'));
    
    if ($json_index == 'Erreur'){           		
      $eqLogic->checkAndUpdateCmd($name, "0");
      die();
    }else{
      $eqLogic->checkAndUpdateCmd($name, "1");
    }

    $json_count = count($json_index);
    if ($json_count == 0){
      $eqLogic->checkAndUpdateCmd($name, "0");
      die();
    }else{
      $eqLogic->checkAndUpdateCmd($name, "1");
    }
    
    for ($i = 0; $i < $json_count; $i++) {
      if ($json_index[$i]['type'] == 'info'){
        $name = $json_index[$i]['nom'];
        $value = $json_index[$i]['value'];            
        $info = $this->getCmd(null, $name); 
        $eqLogic = $info->getEqLogic();      		
        $eqLogic->checkAndUpdateCmd($name, $value);

      }else if ($json_index[$i]['type'] == 'toogle'){
        $name = $json_index[$i]['nom'];          
        $state = $json_index[$i]['state'];            
        $info = $this->getCmd(null, $name);
        $eqLogic = $info->getEqLogic();      		
        $eqLogic->checkAndUpdateCmd($name, $state);

      }else if ($json_index[$i]['type'] == 'range'){
        $name = $json_index[$i]['nom'];
        $value = $json_index[$i]['value'];            
        $info = $this->getCmd(null, 'Value_'.$name);                          	
        $eqLogic = $info->getEqLogic();
        $eqLogic->checkAndUpdateCmd('Value_'.$name, $value); 

      }else if ($json_index[$i]['type'] == 'list'){                  
        $namelist = $json_index[$i]['nomliste'];
        $value = $json_index[$i]['value'];
        $info = $this->getCmd(null, 'Value_'.$namelist);                          	
        $eqLogic = $info->getEqLogic();
        $eqLogic->checkAndUpdateCmd('Value_'.$namelist, $value);

      }else if ($json_index[$i]['type'] == 'color'){
        $id_cmd = $json_index[$i]['id'];
        $name = $json_index[$i]['nom'];
        $color_r = $json_index[$i]['r'];
        $color_g = $json_index[$i]['g'];
        $color_b = $json_index[$i]['b'];
        $hex_value= sprintf("#%02x%02x%02x", $color_r, $color_g, $color_b);            
        $info = $this->getCmd(null, 'Value_'.$name);                          	
        $eqLogic = $info->getEqLogic();
        $eqLogic->checkAndUpdateCmd('Value_'.$name, $hex_value);

      }       
    }     
  }
}



class eebsmCmd extends cmd {  
  
  public function execute($_options = array()) {
    
    $eqLogic = $this->getEqLogic();
	if ($this->getLogicalId() == 'refresh') {
		$eqLogic->refresh();
		return;
	}
    
    switch ($this->getType()) {
		case 'info':
        break;
		case 'action':
        	$cmd = $this->getConfiguration('commande');        	
        	$type = $this->getConfiguration('type');
			
        	if ($type == 'toogle'){
              	$state = $this->getConfiguration('state');
        		$parent = $this->getConfiguration('parent');              
                $req = 'curl -i -XGET "http://'. $eqLogic->getConfiguration('adresseip') . '/update?key=' . $eqLogic->getConfiguration('password') . '&toogle=' . $cmd .'" ';
              	$output = shell_exec($req);                             
                //$eqLogic->checkAndUpdateCmd($parent, $state);
              //throw new Exception(__($req, __FILE__)); 
              	
              
            }else if ($type == 'range'){
              	$parent = $this->getConfiguration('parent');
              	$value = $this->getConfiguration('value');
              	$url = 'http://'. $eqLogic->getConfiguration('adresseip') . '/update?key=' . $eqLogic->getConfiguration('password') . '&range=' . $cmd .'&value='.$_options['slider'];              
              	$url = str_replace(' ', '%20', $url);
                $req = 'curl -i -XGET "'.$url.'" ';
                $output = shell_exec($req);                            
                //$eqLogic->checkAndUpdateCmd($parent, $_options['slider']);
              	
              
            }else if ($type == 'list'){
                $parent = $this->getConfiguration('parent'); 
                $list = $this->getConfiguration('list'); 
                $url = 'http://'. $eqLogic->getConfiguration('adresseip') . '/update?key=' . $eqLogic->getConfiguration('password') . '&list=' . $list .'&value='.$_options['select'];
                $url = str_replace(' ', '%20', $url);
                $req = 'curl -i -XGET "'.$url.'" ';
                $output = shell_exec($req);
              	//$eqLogic->checkAndUpdateCmd($parent,$_options['select']);
              	
              
            }else if ($type == 'bouton'){
              	$url = 'http://'. $eqLogic->getConfiguration('adresseip') . '/update?key=' . $eqLogic->getConfiguration('password') . '&button=' . $cmd;
                $url = str_replace(' ', '%20', $url);
                $req = 'curl -i -XGET "'.$url.'" ';
                $output = shell_exec($req);
              	$eqLogic->refresh();
              	
              
            }else if ($type == 'color'){              
              	$parent = $this->getConfiguration('parent');
              	list($r, $g, $b) = sscanf($_options['color'], "#%02x%02x%02x");              	
              	$url = 'http://'. $eqLogic->getConfiguration('adresseip') . '/update?key=' . $eqLogic->getConfiguration('password') . '&color=' . $cmd . '&r='.$r.'&g='.$g.'&b='.$b;
                $url = str_replace(' ', '%20', $url);
                $req = 'curl -i -XGET "'.$url.'" ';
                $output = shell_exec($req);
              	//$eqLogic->checkAndUpdateCmd($parent, $_options['color']);
              	$eqLogic->refresh();
              	
              
            }else if ($type == 'reboot'){ 
              
              	$url = 'http://'.$eqLogic->getConfiguration('adresseip').'/reboot?key='.$eqLogic->getConfiguration('password');
                $req = 'curl -i -XGET "'.$url.'" ';
                $output = shell_exec($req);
                log::add(__CLASS__, 'debug', 'Redémarrage du module: ' . $result);
              	//eebsm::refresh();
              	             
            }
        
        sleep(0.25);
		$eqLogic->refresh();
              	break;
    }
  }
      
}