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
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

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
  public static function event() {
		
	if (init('value', init('v')) != 'json') {
      	if (init('id') != '') {
			$cmd = eebsmCmd::byId(init('id'));
			if (!is_object($cmd) || $cmd->getEqType() != 'eebsm') {
				throw new Exception(__('Commande ID eebsm inconnu ou la commande n\'est pas de type eebsm', __FILE__) . ' : ' . init('id'));
			}          	
		} 
		if (!is_object($cmd)) {
			throw new Exception(__('Commande introuvable', __FILE__) . ' : ' . json_encode($_GET));
		}
		$type = $cmd->getType();
    	if($type == 'action'){
          log::add('eebsm', 'debug', $cmd->getName()." ACTION HTTP");
              	
          $cmd->execute();
          echo init('id');
        
        }else{
          log::add('eebsm', 'debug', $cmd->getName()." INFO HTTP");
          $cmd->event(init('value', init('v')));
          $eqLogic = $cmd->getEqLogic();
          $type = $cmd->getConfiguration('type');
          
          if ($type == 'info'){
            //$info = $eqLogic->getCmd(null, $name);
            $fils = $cmd->getConfiguration('calcul');
            if ($fils != ''){
              $test = cmd::byString($fils);
              $cmd::byString($fils)->event(init('value', init('v')));              
            }
          }else{
            $name = $cmd->getConfiguration('fils');
            $info = $eqLogic->getCmd(null, $name);
            $id = $info->getId();
            if ($info->getConfiguration('calcul') != ''){
              $result = init('value', init('v'));
              if ($type == "list"){
                  $options_action = array('select'=>$result);
              }else if ($type == "range"){
                  $options_action = array('slider'=>$result);
              }else if ($type == "color"){
                  $options_action = array('color'=>$result);
              }
             cmd::byId($id)->execCmd($options_action, $cache=0);
            }	
          }
         echo init('id');
        }
    }else{
        if (init('id') != '') {
			$cmd = eebsmCmd::byId(init('id'));
			if (!is_object($cmd) || $cmd->getEqType() != 'eebsm') {
				throw new Exception(__('Commande ID eebsm inconnu ou la commande n\'est pas de type eebsm', __FILE__) . ' : ' . init('id'));
			}
		} 
      	$json = $cmd->getConfiguration('json');
      	echo $json;
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
    $retour_module = $this->GetModule();    
    if ($retour_module != "Erreur" && $retour_module != ""){          
      $this->setConfiguration('module',$retour_module);
    }else{
      throw new Exception(__("Oups! N'y aurait-il pas un petit problème dans la saisie des informations?", __FILE__));
      die();
    }
    
    if ($this->getConfiguration('adresseip') == '') {
      throw new Exception(__("L'adresse IP ne peut pas être vide", __FILE__));
    }else{
      	if ($this->getConfiguration('password') == '') {
      		throw new Exception(__("Le mot de passe ne peut pas être vide", __FILE__));
    	}else{
          	
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
          log::add(__CLASS__, 'debug', "DEBUT");
              
          
          	$refresh = $this->getCmd(null, 'refresh');
            if (!is_object($refresh)) {
                $refresh = new eebsmCmd();
                $refresh->setName(__('Rafraichir', __FILE__));
              	$refresh->setEqLogic_id($this->getId());
                $refresh->setLogicalId('refresh');
                $refresh->setType('action');
                $refresh->setSubType('other');
              	$refresh->setConfiguration('type','refresh');
                $refresh->setIsVisible(1);
                $refresh->setConfiguration('visible_cmd',"1");
                $refresh->setConfiguration('commande',"-1");
            }
            $refresh->setOrder($ordre);
          	$ordre++;
            $refresh->save();
          
            $name = "Init cmd";            
            $init_cmd = $this->getCmd(null, 'init_cmd');
            if (!is_object($init_cmd)) {
              log::add(__CLASS__, 'debug', "Ajout du bouton d'initialisation");
              $init_cmd = new eebsmCmd();
              $init_cmd->setName(__($name, __FILE__));
              $init_cmd->setLogicalId('init_cmd');                
              $init_cmd->setEqLogic_id($this->getId());
              if($this->getConfiguration('widgets') == '1') $init_cmd->setTemplate('dashboard', 'eebsm_button');            
              if($this->getConfiguration('widgets') == '1') $init_cmd->setTemplate('mobile', 'eebsm_button');            
              $init_cmd->setType('action');
              $init_cmd->setSubType('other');
              $init_cmd->setConfiguration('type','init cmd');
              $init_cmd->setIsVisible(0);
              $init_cmd->setConfiguration('visible_cmd',"0");
              $init_cmd->setConfiguration('commande',"-1");
            }
          	$init_cmd->setOrder($ordre);
          	$ordre++;
            $init_cmd->save();
          
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
          
          	//Création des commandes
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
                  	
                }else if ($json_index[$i]['type'] == 'toggle'){
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
                        $info->setConfiguration('type','toggle');
                        $info->setConfiguration('visible_cmd',"1");                      	
                        $info->setSubType('binary');
                        $info->setIsVisible(0);
                    }                    
                    $info->setOrder($ordre);
          			$ordre++;
                  	$info->setConfiguration('state',$state);
          			$info->setConfiguration('commande',$id_0);
                    $info->setConfiguration('id_0',$id_0);
                    $info->setConfiguration('id_1',$id_1);
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
                        $cmd_0->setConfiguration('type','toggle');
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
                        $cmd_1->setConfiguration('type','toggle');
                        $cmd_1->setConfiguration('state','On');  
                        $cmd_1->setConfiguration('visible_cmd',"1");
                    }                    
                    $cmd_1->setOrder($ordre);
          			$ordre++;
          			$cmd_1->setvalue($info_id);          
                    $cmd_1->setConfiguration('parent',$name);
                    $cmd_1->setConfiguration('commande',$id_1);                        		 
                    $cmd_1->save();
                  
                  	$info->setConfiguration('fils0',$name_0);
                  	$info->setConfiguration('fils1',$name_1);
                    $info->save();
					
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
                        $info->setConfiguration('type','range');
                        $info->setSubType('numeric');                        
                        $info->setIsVisible(0);
                        $info->setConfiguration('visible_cmd',"1");
                    } 
                    $info->setOrder($ordre);
          			$ordre++;
          			$info->setConfiguration('value',$value);
                    $info->setConfiguration('commande',$id_cmd);
                    $info->setConfiguration('minValue', $min);
                    $info->setConfiguration('maxValue', $max);
                    $info->save();
                                     	
                    $info_id = $info->getId();                  	
                    $cmd = $this->getCmd(null, $name);
                  	if (!is_object($cmd)) {
                        $cmd = new eebsmCmd();
                        $cmd->setName(__($name, __FILE__));
                      	$cmd->setLogicalId($name);
                        $cmd->setEqLogic_id($this->getId());
                        if($this->getConfiguration('widgets') == '1') $cmd->setTemplate('dashboard', 'eebsm_slider');
                        if($this->getConfiguration('widgets') == '1') $cmd->setTemplate('mobile', 'eebsm_slider');
                        $cmd->setType('action');
                        $cmd->setSubType('slider');
                        $cmd->setConfiguration('type','range');
                        $cmd->setConfiguration('visible_cmd',"1");
                    }  
                    $cmd->setOrder($ordre);
          			$ordre++;
          			$cmd->setConfiguration('minValue', $min);
                    $cmd->setConfiguration('maxValue', $max);
                    $cmd->setConfiguration('commande',$id_cmd);
                    $cmd->setConfiguration('parent','Value_'.$name);
                    
                    $cmd->setValue($info_id);                        
                    $cmd->save(); 
                  
                  	$info->setConfiguration('fils',$name);
                  	$info->save();
                  	log::add(__CLASS__, 'debug', "Finish" . $name);
                  
                  
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
                        $info->setConfiguration('type','list');
                        $info->setSubType('string');
                        $info->setIsVisible(0);
                        $info->setConfiguration('visible_cmd',"1");
                    }                    
                    $info->setOrder($ordre);
          			$ordre++;
          			$info->setConfiguration('commande',$id_cmd);
                    $info->setConfiguration('listname',$namelist); 
                    $info->save();
                                           
                  	$info_id = $info->getId();                  	
                    $cmd = $this->getCmd(null, $namelist);
                  	if (!is_object($cmd)) {
                        $cmd = new eebsmCmd();
                        $cmd->setName(__($namelist, __FILE__));
                        $cmd->setLogicalId($namelist);
                        $cmd->setEqLogic_id($this->getId());
                        $cmd->setType('action');
                        $cmd->setSubType('select');
                        $cmd->setConfiguration('type','list');
                        $cmd->setConfiguration('visible_cmd',"1");
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
                  	$cmd->setOrder($ordre);
          			$ordre++;
          			$final_list = substr($work_list, 0, -1);                    
                    $cmd->setConfiguration('listValue', $final_list);
                    $cmd->setConfiguration('commande',$id_cmd);
                  	$cmd->setConfiguration('list',$namelist);
                    $cmd->setConfiguration('parent','Value_'.$namelist);
                    $cmd->setConfiguration('state',$name);
                    $cmd->setValue($info_id);  
                    $cmd->save();
                  
                  	$info->setConfiguration('fils',$namelist);
                  	$info->save();
                  
                  
                }else if ($json_index[$i]['type'] == 'button'){
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
                        $info->setConfiguration('type','button');
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
                        $info->setConfiguration('type','color');
                        $info->setType('info');
                        $info->setSubType('string');
                        $info->setIsVisible(0);
                      	$info->setConfiguration('visible_cmd',"1");
                    }
                    
                    $info->setOrder($ordre);
          			$ordre++;          			
                    $info->setConfiguration('commande',$id_cmd);
                    $info->save();
                                    
                  	$info_id = $info->getId();                  
                  	$cmd = $this->getCmd(null, $name);
                    if (!is_object($cmd)) {
                        $cmd = new eebsmCmd();
                        $cmd->setName(__($name, __FILE__));
                      	$cmd->setLogicalId($name);
                        $cmd->setEqLogic_id($this->getId());
                        if($this->getConfiguration('widgets') == '1') $cmd->setTemplate('dashboard', 'eebsm_color');
                        if($this->getConfiguration('widgets') == '1') $cmd->setTemplate('mobile', 'eebsm_color');
                        $cmd->setType('action');
                        $cmd->setSubType('color');
                      	$cmd->setConfiguration('type','color');
                      	$cmd->setConfiguration('parameters', 'color=FFFFFF');
                    	$cmd->setConfiguration('visible_cmd',"1");
                    }
                    
                    $cmd->setOrder($ordre);
          			$ordre++;
          			$cmd->setConfiguration('commande',$id_cmd);
                  	$cmd->setConfiguration('parent','Value_'.$name);
                    $cmd->setConfiguration('state',$hex_value);
                    $cmd->setValue($info_id);                  	
                    $cmd->save();
                  
                  	$info->setConfiguration('fils',$name);
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
          
          	$eebsm_key_eebsm = eebsm::GetParam('Jeedom_Key_eebsm');
          	log::add(__CLASS__, 'debug', 'Clé eebsm décryptée: '.$eebsm_key_eebsm);
          
            $eebsm_refresh = eebsm::GetParam('Jeedom_Refresh');
            log::add(__CLASS__, 'debug', 'Refresh paramétré: '.$eebsm_refresh);
          
            $eebsm_init = eebsm::GetParam('Jeedom_Init_Cmd');
            log::add(__CLASS__, 'debug', 'Init cmd paramétré: '.$eebsm_init);
          
            $adresseip = network::getNetworkAccess();
          	$cleapi = jeedom::getApiKey();
            $cleapi_eebsm = jeedom::getApiKey('eebsm');
            $idrefresh = $refresh->getId(); 
          	$idinitcmd = $init_cmd->getId(); 
          	
          	
          	$update = 0;
            if ($eebsm_plugin == "Null" || $eebsm_plugin == "Erreur" || $eebsm_adresseip == "Null" || $eebsm_adresseip == "Erreur" || $eebsm_key == "Null" || $eebsm_key == "Erreur" || $eebsm_key_eebsm == "Null" || $eebsm_key_eebsm == "Erreur" || $eebsm_refresh == "Null" || $eebsm_refresh == "Erreur" || $eebsm_init == "Null" || $eebsm_init == "Erreur"){
              
              log::add(__CLASS__, 'debug', 'Création des paramètres SPIFFS');
              if ($eebsm_plugin == "Null" || $eebsm_plugin == "Erreur" ){
              	$result = eebsm::AddListParamBool('Jeedom',"1");
              	log::add(__CLASS__, 'debug', 'Ajout du paramètre Jeedom: 1 : '.$result);
              }

              if ($eebsm_adresseip == "Null" || $eebsm_adresseip == "Erreur" ){
                $result = eebsm::AddListParam('Jeedom_IP',$adresseip);
                log::add(__CLASS__, 'debug', 'Ajout du paramètre Jeedom_IP: '.$adresseip.' : '.$result);
              }

              if ($eebsm_key == "Null" || $eebsm_key == "Erreur"){
                $result = eebsm::AddListParamCrypted('Jeedom_Key',$cleapi);
                log::add(__CLASS__, 'debug', 'Ajout du paramètre Jeedom_Key crypté: '.$cleapi.' : '.$result);
              }
              
              if ($eebsm_key_eebsm == "Null" || $eebsm_key == "Erreur"){
                $result = eebsm::AddListParamCrypted('Jeedom_Key_eebsm',$cleapi_eebsm);
                log::add(__CLASS__, 'debug', 'Ajout du paramètre Jeedom_Key_eebsm crypté: '.$cleapi_eebsm.' : '.$result);
              }

              if ($eebsm_refresh == "Null" || $eebsm_refresh == "Erreur"){
                $result = eebsm::AddListParam('Jeedom_Refresh',$idrefresh);
                log::add(__CLASS__, 'debug', 'Ajout du paramètre Jeedom_Refresh: '.$idrefresh.' : '.$result);
              }
              
              if ($eebsm_init == "Null" || $eebsm_init == "Erreur"){
                $result = eebsm::AddListParam('Jeedom_Init_Cmd',$idinitcmd);
                log::add(__CLASS__, 'debug', 'Ajout du paramètre Jeedom_Init_Cmd: '.$idinitcmd.' : '.$result);
              }
              
              eebsm::CreateListParam();
              
            }else{
              if ($eebsm_plugin != '1'){
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
              if ($cleapi_eebsm != $eebsm_key_eebsm){
                $update++;
                $result = eebsm::UpdateListParam('Jeedom_Key_eebsm',$cleapi_eebsm);
                log::add(__CLASS__, 'debug', 'Modification du paramètre Jeedom_Key_eebsm crypté: '.$cleapi_eebsm.' : '.$result);
              }
              if ($idrefresh != $eebsm_refresh){				
                $update++;
                $result = eebsm::UpdateListParam('Jeedom_Refresh',$idrefresh);
                log::add(__CLASS__, 'debug', 'Modification du paramètre Jeedom_Refresh: '.$idrefresh.' : '.$result);							
              }
              
              if ($idinitcmd != $eebsm_init){				
                $update++;
                $result = eebsm::UpdateListParam('Jeedom_Init_Cmd',$idinitcmd);
                log::add(__CLASS__, 'debug', 'Modification du paramètre Jeedom_Init_Cmd: '.$idinitcmd.' : '.$result);							
              }
              
              if ($update>0) eebsm::SaveListParam();

            }
          	eebsm::Init_eebsm_Module();
          	
          log::add(__CLASS__, 'debug', 'Sauvegarde OK');	
        }
    }
  }

    public function Init_eebsm_Module() {
    
    $json_index = $this->GetIndex(true);
    //log::add('eebsm', 'debug', json_encode($json_index));
    
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

    $json_count = count($json_index['actions']);
    if ($json_count == 0){
      $eqLogic->checkAndUpdateCmd($name, "0");
      die();
    }else{
      $eqLogic->checkAndUpdateCmd($name, "1");
    }    
    
    log::add(__CLASS__, 'debug', $json_count);	
    for ($i = 0; $i < $json_count; $i++) {
      if ($json_index['actions'][$i]['type'] == 'toggle'){					
          $id = $json_index['actions'][$i]['id0'];
          $name = $json_index['actions'][$i]['nom'];                    
          $info_on = $this->getCmd(null, 'On_'.$name);
          $info_off = $this->getCmd(null, 'Off_'.$name);
          $eqLogic_on = $info_on->getId();        	
          $eqLogic_off = $info_off->getId();
          $json_index['actions'][$i]['jeedom0'] = $eqLogic_off;
          $json_index['actions'][$i]['jeedom1'] = $eqLogic_on;
          log::add(__CLASS__, 'debug', $id.' : '.$eqLogic_on.' '.$eqLogic_off);	
          
      }else if ($json_index['actions'][$i]['type'] == 'info' || $json_index['actions'][$i]['type'] == 'range' || $json_index['actions'][$i]['type'] == 'list' || $json_index['actions'][$i]['type'] == 'button' || $json_index['actions'][$i]['type'] == 'color'){
          $id = $json_index['actions'][$i]['id'];          
          if ($json_index['actions'][$i]['type'] == 'info' || $json_index['actions'][$i]['type'] == 'button'){
          	$name = $json_index['actions'][$i]['nom'];             
          } else if ($json_index['actions'][$i]['type'] == 'list'){
          	$name = 'Value_'.$json_index['actions'][$i]['nomliste'];            
          } else {
          	$name = 'Value_'.$json_index['actions'][$i]['nom'];            
          }
          $info = $this->getCmd(null, $name);
          $eqLogic = $info->getId();   
          $json_index['actions'][$i]['jeedom'] = $eqLogic;
          log::add(__CLASS__, 'debug', $id.' : '.$eqLogic);	
      }
      
      //MAJ
      if ($json_index['actions'][$i]['type'] == 'info'){          
				
          $name = $json_index['actions'][$i]['nom'];
          $value = $json_index['actions'][$i]['value'];            
          $info = $this->getCmd(null, $name); 
          $eqLogic = $info->getEqLogic();        	
          $eqLogic->checkAndUpdateCmd($name, $value);
          
        
      }else if ($json_index['actions'][$i]['type'] == 'toggle'){
        $name = $json_index['actions'][$i]['nom'];          
        $state = $json_index['actions'][$i]['state'];            
        $info = $this->getCmd(null, $name);
        $eqLogic = $info->getEqLogic();
                
        $value_actu = jeedom::evaluateExpression('#'.$info->getId().'#');
        if ($state == 'Off')$state = '0';
        else $state = '1';        
        if ($value_actu != $state){
          if ($value_actu == '0'){
            $fils_name = $info->getConfiguration('fils1');          
          }else{
            $fils_name = $info->getConfiguration('fils0');
          }
          $fils = $this->getCmd(null, $fils_name);
          $calcul = $fils->getConfiguration('calcul');
          if ( $calcul != ''){          
            cmd::byString($calcul)->execCmd(null, $cache=0);
          }
          $eqLogic->checkAndUpdateCmd($name, $state);
        }

      }else if ($json_index['actions'][$i]['type'] == 'range'){
        $name = $json_index['actions'][$i]['nom'];
        $value = $json_index['actions'][$i]['value'];            
        $info = $this->getCmd(null, 'Value_'.$name);                          	
        $eqLogic = $info->getEqLogic();
        
        $value_actu = jeedom::evaluateExpression('#'.$info->getId().'#');         
        if ($value_actu != $value){          
          $fils_name = $info->getConfiguration('fils');          
          $fils = $this->getCmd(null, $fils_name);
          $calcul = $fils->getConfiguration('calcul');
          if ( $calcul != ''){
            $options_action = array('slider'=>$value);
            cmd::byString($calcul)->execCmd($options_action, $cache=0);
          }
          $eqLogic->checkAndUpdateCmd('Value_'.$name, $value);
        }

      }else if ($json_index['actions'][$i]['type'] == 'list'){                  
        $namelist = $json_index['actions'][$i]['nomliste'];
        $value = $json_index['actions'][$i]['value'];
        $info = $this->getCmd(null, 'Value_'.$namelist);                          	
        $eqLogic = $info->getEqLogic();
        
        $value_actu = jeedom::evaluateExpression('#'.$info->getId().'#');         
        if ($value_actu != $value){          
          $fils_name = $info->getConfiguration('fils');          
          $fils = $this->getCmd(null, $fils_name);
          $calcul = $fils->getConfiguration('calcul');
          if ( $calcul != ''){            
            $options_action = array('select'=>$value);
            cmd::byString($calcul)->execCmd(null, $cache=0);
          }
          $eqLogic->checkAndUpdateCmd('Value_'.$namelist, $value);
        }
        

      }else if ($json_index['actions'][$i]['type'] == 'color'){
        $id_cmd = $json_index['actions'][$i]['id'];
        $name = $json_index['actions'][$i]['nom'];
        $color_r = $json_index['actions'][$i]['r'];
        $color_g = $json_index['actions'][$i]['g'];
        $color_b = $json_index['actions'][$i]['b'];
        $hex_value= sprintf("#%02x%02x%02x", $color_r, $color_g, $color_b);            
        $info = $this->getCmd(null, 'Value_'.$name);                          	
        $eqLogic = $info->getEqLogic();
        
        $value_actu = jeedom::evaluateExpression('#'.$info->getId().'#');         
        if ($value_actu != $hex_value){          
          $fils_name = $info->getConfiguration('fils');          
          $fils = $this->getCmd(null, $fils_name);
          $calcul = $fils->getConfiguration('calcul');
          if ( $calcul != ''){
            $options_action = array('color'=>$hex_value);
            cmd::byString($calcul)->execCmd($options_action, $cache=0);
          }
          $eqLogic->checkAndUpdateCmd('Value_'.$name, $hex_value);
        }
      } 
      
            
    }
    $name = "init_cmd";
    $info = eebsm::getCmd(null, $name);
    $id = $info->getId();     
    $info->setConfiguration('json',json_encode($json_index));
    $info->save();
    log::add('eebsm', 'debug', $id);
    eebsm::SendJeedomIDOrder($id);
    
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
  
  
  public function SendJeedomIDOrder($value) {
    $url = 'http://'.$this->getConfiguration('adresseip').'/sendjeedomorderid?key='.$this->getConfiguration('password').'&value='.$value;
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
  
  /*public function SetJeedomId($param,$value) {
    $url = 'http://'.$this->getConfiguration('adresseip').'/setjeedomid?key='.$this->getConfiguration('password').'&id='.$param.'&value='.$value;
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
  
  public function SetJeedomToggleId($param,$value0, $value1) {
    $url = 'http://'.$this->getConfiguration('adresseip').'/setjeedomtoggleid?key='.$this->getConfiguration('password').'&id='.$param.'&value0='.$value0.'&value1='.$value1;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        return "Erreur";		  
    }
    curl_close($ch);
    return $result;    
  }*/
  
  
  public function UpdateListParam($param,$value) {
    $url = 'http://'.$this->getConfiguration('adresseip').'/updatelistparam?key='.$this->getConfiguration('password').'&param='.$param.'&value='.$value;
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
  
  
  
  public function SaveListParam() {
    $url = 'http://'.$this->getConfiguration('adresseip').'/savelistparam?key='.$this->getConfiguration('password');
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
    $url = 'http://'.$this->getConfiguration('adresseip').'/addlistparam?key='.$this->getConfiguration('password').'&param='.$param.'&value='.$value.'&type=toggle';
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
    return $result; 
  }
  
  public function GetIndex($brut = false) {
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
    if ($brut) return $jsonData;
    else return $table;
  }
  
  public function AddListLvgl($type,$name,$loc,$min,$max,$value,$unit) {
    $url = 'http://'.$this->getConfiguration('adresseip').'/setjeedomid?key='.$this->getConfiguration('password').'&type='.$type.'&name='.$name.'&loc='.$loc.'&min='.$min.'&max='.$max.'&value='.$value.'&unit='.$unit;
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
          
        
      }else if ($json_index[$i]['type'] == 'toggle'){
        $name = $json_index[$i]['nom'];          
        $state = $json_index[$i]['state'];            
        $info = $this->getCmd(null, $name);
        $eqLogic = $info->getEqLogic();
                
        $value_actu = jeedom::evaluateExpression('#'.$info->getId().'#');
        if ($state == 'Off')$state = '0';
        else $state = '1';        
        if ($value_actu != $state){
          if ($value_actu == '0'){
            $fils_name = $info->getConfiguration('fils1');          
          }else{
            $fils_name = $info->getConfiguration('fils0');
          }
          $fils = $this->getCmd(null, $fils_name);
          $calcul = $fils->getConfiguration('calcul');
          if ( $calcul != ''){          
            cmd::byString($calcul)->execCmd(null, $cache=0);
          }
          $eqLogic->checkAndUpdateCmd($name, $state);
       }

      }else if ($json_index[$i]['type'] == 'range'){
        $name = $json_index[$i]['nom'];
        $value = $json_index[$i]['value'];            
        $info = $this->getCmd(null, 'Value_'.$name);                          	
        $eqLogic = $info->getEqLogic();
        
        $value_actu = jeedom::evaluateExpression('#'.$info->getId().'#');         
        if ($value_actu != $value){          
          $fils_name = $info->getConfiguration('fils');          
          $fils = $this->getCmd(null, $fils_name);
          $calcul = $fils->getConfiguration('calcul');
          if ( $calcul != ''){
            $options_action = array('slider'=>$value);
            cmd::byString($calcul)->execCmd($options_action, $cache=0);
          }
          $eqLogic->checkAndUpdateCmd('Value_'.$name, $value);
        }

      }else if ($json_index[$i]['type'] == 'list'){                  
        $namelist = $json_index[$i]['nomliste'];
        $value = $json_index[$i]['value'];
        $info = $this->getCmd(null, 'Value_'.$namelist);                          	
        $eqLogic = $info->getEqLogic();
        
        $value_actu = jeedom::evaluateExpression('#'.$info->getId().'#');         
        if ($value_actu != $value){          
          $fils_name = $info->getConfiguration('fils');          
          $fils = $this->getCmd(null, $fils_name);
          $calcul = $fils->getConfiguration('calcul');
          if ( $calcul != ''){            
            $options_action = array('select'=>$value);
            cmd::byString($calcul)->execCmd(null, $cache=0);
          }
          $eqLogic->checkAndUpdateCmd('Value_'.$namelist, $value);
        }        

      }else if ($json_index[$i]['type'] == 'color'){
        $id_cmd = $json_index[$i]['id'];
        $name = $json_index[$i]['nom'];
        $color_r = $json_index[$i]['r'];
        $color_g = $json_index[$i]['g'];
        $color_b = $json_index[$i]['b'];
        $hex_value= sprintf("#%02x%02x%02x", $color_r, $color_g, $color_b);            
        $info = $this->getCmd(null, 'Value_'.$name);                          	
        $eqLogic = $info->getEqLogic();
        
        $value_actu = jeedom::evaluateExpression('#'.$info->getId().'#');         
        if ($value_actu != $hex_value){          
          $fils_name = $info->getConfiguration('fils');          
          $fils = $this->getCmd(null, $fils_name);
          $calcul = $fils->getConfiguration('calcul');
          if ( $calcul != ''){
            $options_action = array('color'=>$hex_value);
            cmd::byString($calcul)->execCmd($options_action, $cache=0);
          }
          $eqLogic->checkAndUpdateCmd('Value_'.$name, $hex_value);
        }		
      }      
    }     
  }
}



class eebsmCmd extends cmd { 
  
  
  public function preSave() {  
    $calcul = $this->getConfiguration('calcul');
    if ($this->getType() == 'info') {      
			$this->setValue($calcul);
	}
  }
  
  public function postSave() {
		if ($this->getType() == 'info' && $this->getConfiguration('type') == 'info' &&$this->getConfiguration('calcul') != '') {			
        	$this->event($this->execute());
		}
	}
  
 public function execute($_options = array()) {
    $eqLogic = $this->getEqLogic();
	if ($this->getLogicalId() == 'refresh') {
		$eqLogic->refresh();
        //$eqLogic->Init_eebsm_Module();
      	return;
	}
   
   	if ($this->getLogicalId() == 'init_cmd') {
		$eqLogic->Init_eebsm_Module();
		return;
	}
    
    switch ($this->getType()) {
		case 'info':
        $type = $this->getConfiguration('type');
        $nom = $this->getLogicalId();
        
        
        if ($this->getConfiguration('calcul') != ''&& $this->getConfiguration('type') == 'info'){
           
          $result = jeedom::evaluateExpression($this->getConfiguration('calcul'));           
		  $cmd = $this->getConfiguration('commande');
          $url = 'http://'. $eqLogic->getConfiguration('adresseip') . '/updatenr?key=' . $eqLogic->getConfiguration('password') . '&info=' . $cmd .'&value='.$result; 
          log::add('eebsm', 'debug', $eqLogic->getName().': INFO: info: ' . $nom .": ".$url);
          $req = 'curl -i -XGET "'.$url.'" ';
          $output = shell_exec($req);          	
          return $result;            
        
        }else if ($this->getConfiguration('calcul') != '' && $this->getConfiguration('type') == 'toggle'){
          $result = jeedom::evaluateExpression($this->getConfiguration('calcul'));           
		  $id_0 = $this->getConfiguration('id_0');
          $id_1 = $this->getConfiguration('id_1');          
          if ($result == 0 || $result == "off" || $result == "Off"){
            $url = 'http://'. $eqLogic->getConfiguration('adresseip') . '/updatenr?key=' . $eqLogic->getConfiguration('password') . '&toggle=' . $id_0;
          }else{
            $url = 'http://'. $eqLogic->getConfiguration('adresseip') . '/updatenr?key=' . $eqLogic->getConfiguration('password') . '&toggle=' . $id_1;           
          }
          log::add('eebsm', 'debug', $eqLogic->getName().': INFO: toogle: ' . $nom .": ".$url);
          $req = 'curl -i -XGET "'.$url.'" ';
          $output = shell_exec($req);          	
          return $result;
          
        }else if ($this->getConfiguration('calcul') != '' && $this->getConfiguration('type') == 'list'){
          $result = jeedom::evaluateExpression($this->getConfiguration('calcul'));
          
		  $cmd = $this->getConfiguration('commande');
          $listname = $this->getConfiguration('listname');
          $url = 'http://'. $eqLogic->getConfiguration('adresseip') . '/updatenr?key=' . $eqLogic->getConfiguration('password') . '&list=' . $listname .'&value='.$result;              
          log::add('eebsm', 'debug', $eqLogic->getName().': INFO: list: ' . $nom .": ".$url);
          $req = 'curl -i -XGET "'.$url.'" ';
          $ch = curl_init();
    	  curl_setopt($ch, CURLOPT_URL, $url);
    	  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	  $output = curl_exec($ch);
    	  if (curl_errno($ch)) {
        	  return "Erreur";
		  }
    	  curl_close($ch);          
          if ($output == "Erreur"){
            return "Item inexistant";
          }else{
            return $output;
          }
          
        }else if ($this->getConfiguration('calcul') != ''&& $this->getConfiguration('type') == 'range'){           
          $result = jeedom::evaluateExpression($this->getConfiguration('calcul'));           
		  $cmd = $this->getConfiguration('commande');
          $url = 'http://'. $eqLogic->getConfiguration('adresseip') . '/updatenr?key=' . $eqLogic->getConfiguration('password') . '&range=' . $cmd .'&value='.$result;              
          log::add('eebsm', 'debug', $eqLogic->getName().': INFO: range: ' . $nom .": ".$url);
          $req = 'curl -i -XGET "'.$url.'" ';
          $output = shell_exec($req);          	
          return $result;            
        
        }else if ($this->getConfiguration('calcul') != ''&& $this->getConfiguration('type') == 'color'){           
          $result = jeedom::evaluateExpression($this->getConfiguration('calcul'));           
		  list($r, $g, $b) = sscanf($result, "#%02x%02x%02x");
          $cmd = $this->getConfiguration('commande');
          $url = 'http://'. $eqLogic->getConfiguration('adresseip') . '/updatenr?key=' . $eqLogic->getConfiguration('password') . '&color=' . $cmd . '&r='.$r.'&g='.$g.'&b='.$b;             
          log::add('eebsm', 'debug', $eqLogic->getName().': INFO: color: ' . $nom .": ".$url);
          $req = 'curl -i -XGET "'.$url.'" ';
          $output = shell_exec($req);          	
          return $result;            
        }
        
        break;
		case 'action':
        $nom = $this->getLogicalId();          
        	$cmd = $this->getConfiguration('commande');        	
        	$type = $this->getConfiguration('type');
			
        	if ($type == 'toggle'){
              	$state = $this->getConfiguration('state');
        		$parent = $this->getConfiguration('parent');              
                $url = 'http://'. $eqLogic->getConfiguration('adresseip') . '/updatenr?key=' . $eqLogic->getConfiguration('password') . '&toggle=' . $cmd;
                log::add('eebsm', 'debug', $eqLogic->getName().": ACTION: toogle: ".$nom.": " . $url);
              	$req = 'curl -i -XGET "'.$url.'" ';
                
              $output = shell_exec($req);                             
                $eqLogic->checkAndUpdateCmd($parent, $state);
              	if ($this->getConfiguration('calcul') != ''){
                	$result = jeedom::evaluateExpression($this->getConfiguration('calcul'));
                	cmd::byString($result)->execCmd(null, $cache=0);
              	}
              	log::add('eebsm', 'debug', $eqLogic->getName().": ACTION: retour: " . $url);
              	break;
              
            }else if ($type == 'range'){
              	$parent = $this->getConfiguration('parent');
              	$value = $this->getConfiguration('value');
              	$url = 'http://'. $eqLogic->getConfiguration('adresseip') . '/updatenr?key=' . $eqLogic->getConfiguration('password') . '&range=' . $cmd .'&value='.$_options['slider'];              
              	$url = str_replace(' ', '%20', $url);
                $req = 'curl -i -XGET "'.$url.'" ';
                $output = shell_exec($req);
                $eqLogic->checkAndUpdateCmd($parent, $_options['slider']);
              	if ($this->getConfiguration('calcul') != ''){
                	$result = jeedom::evaluateExpression($this->getConfiguration('calcul'));
                	log::add('eebsm', 'debug', $result);
          			$options_action = array('slider'=>$_options['slider']);
					cmd::byString($result)->execCmd($options_action, $cache=0);
              	}
              	log::add('eebsm', 'debug', $url);
        		break;
              
            }else if ($type == 'list'){
                $parent = $this->getConfiguration('parent'); 
                $list = $this->getConfiguration('list'); 
                $url = 'http://'. $eqLogic->getConfiguration('adresseip') . '/updatenr?key=' . $eqLogic->getConfiguration('password') . '&list=' . $list .'&value='.$_options['select'];
                $url = str_replace(' ', '%20', $url);
                $req = 'curl -i -XGET "'.$url.'" ';
                $output = shell_exec($req);
              	$eqLogic->checkAndUpdateCmd($parent,$_options['select']);
              
                if ($this->getConfiguration('calcul') != ''){
                	$result = jeedom::evaluateExpression($this->getConfiguration('calcul'));
                  
                	$options_action = array('select'=>$_options['select']);
					cmd::byString($result)->execCmd($options_action, $cache=0);
              	}
              	log::add('eebsm', 'debug', $url);
        		break;
              
            }else if ($type == 'button'){
              	$url = 'http://'. $eqLogic->getConfiguration('adresseip') . '/updatenr?key=' . $eqLogic->getConfiguration('password') . '&button=' . $cmd;
                $url = str_replace(' ', '%20', $url);
                $req = 'curl -i -XGET "'.$url.'" ';
                $output = shell_exec($req);
              	if ($this->getConfiguration('calcul') != ''){
                	$result = jeedom::evaluateExpression($this->getConfiguration('calcul'));
                	cmd::byString($result)->execCmd(null, $cache=0);
              	}
              	log::add('eebsm', 'debug', $url);
        		break;
              
            }else if ($type == 'color'){              
              	$parent = $this->getConfiguration('parent');
              	list($r, $g, $b) = sscanf($_options['color'], "#%02x%02x%02x");              	
              	$url = 'http://'. $eqLogic->getConfiguration('adresseip') . '/updatenr?key=' . $eqLogic->getConfiguration('password') . '&color=' . $cmd . '&r='.$r.'&g='.$g.'&b='.$b;
                $url = str_replace(' ', '%20', $url);
                $req = 'curl -i -XGET "'.$url.'" ';
                $output = shell_exec($req);
              	$eqLogic->checkAndUpdateCmd($parent, $_options['color']);
                if ($this->getConfiguration('calcul') != ''){
                	$result = jeedom::evaluateExpression($this->getConfiguration('calcul'));
                	$options_action = array('color'=>$_options['color']);
					cmd::byString($result)->execCmd($options_action, $cache=0);
              	}
              log::add('eebsm', 'debug', $url);
        		break;
              	              
            }else if ($type == 'reboot'){ 
              
              	$url = 'http://'.$eqLogic->getConfiguration('adresseip').'/reboot?key='.$eqLogic->getConfiguration('password');
                $req = 'curl -i -XGET "'.$url.'" ';
                $output = shell_exec($req);
                log::add(__CLASS__, 'debug', 'Redémarrage du module: ' . $result);
              	
              	break;           
            }
        
    }
  }      
}