<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
// Déclaration des variables obligatoires
$plugin = plugin::byId('eebsm');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
log::add('eebsm', 'debug', "Lancement script");
          
?>

                 
            	<div id="PopupDiv">
                  	<div class='eebsmright'><div class='eebsmbutton dangerbutton' id="eebsmbuttonannuler"><i style="margin-left:-1px;" class='icon kiko-cross'></i></div></div>  
  					<label  style='margin-top:10px;'class="control-label">{{Pour débuter la programmation, veuillez saisir les informations de connexion.}}</label>     
    				<div class="eebsminputzone"> 
  					<div id="diveebsmip" style="margin-top:2px; display:flex; line-height:40px; vertical-align: middle;"><div  style="width:30%;">Adresse IP&nbsp;:&nbsp;</div><input style="width:70%;" type="text" id="eebsmip" size="12" /></div>
                    <div id="diveebsmmdp" style="margin-top:2px; display:flex; line-height:40px; vertical-align: middle;"><div  style="width:30%;">Mot de passe&nbsp;:&nbsp;</div><input style="width:70%;" type="password" id="eebsmmdp" size="12" /></div> 
                    </div>
                    <div id="eebsmboutontester" class="eebsmbutton" style="background-color:var(--al-warning-color); width:95%; margin-top:10px;">Tester la connexion</div> 
                    <label id='labelcmd' style='margin-top:10px;'class="control-label">{{Quel type de commande voulez-vous ajouter à l'écran?}}</label>      <!-- ' -->
    				<select id="eebsmtype">
                        <option value="nope">-- Veuillez sélectionner un type de commande --</option>
                        <option value="datetime">Date et heure</option>
        				<option value="info">Info</option>
        				<option value="button">Bouton</option>
        				<option value="toggle">Bouton basculant</option>
                        <option value="range">Curseur</option>
                        <option value="roundrange">Curseur arrondi</option>
                        <option value="color">Couleur</option>
                        <option value="list">Liste</option>
                        <option value="listbutton">Liste vers boutons</option>
    				</select>                    
                      <span style="width:90%;" id="eebsmemplacement">
                      <label class="control-label">{{Emplacement à l'écran}}</label>      <!-- ' -->
    					<select  id="emplacementSelect">
                          <option value="nope">-- Veuillez sélectionner un emplacement --</option>
                          <option value="accueil">Accueil</option>
                          <option value="temperatures">Températures</option>
                          <option value="lumieres">Lumières</option>                        
    					</select>
                      </span>                        
                      <div class="eebsminputzone"> 
                        
                        <div id="diveebsmname" style="margin-top:2px; display:flex; line-height:40px; vertical-align: middle;"><div align=right style="width:30%;">{{Nom}}:&nbsp;</div><input style="width:70%;" type="text" id="eebsmname" required minlength="1" size="12" /></div>
                        <div id="diveebsmmin" style="margin-top:2px; display:flex; line-height:40px; vertical-align: middle;"><div align=right style="width:30%;">{{Valeur mini}}:&nbsp;</div><input style="width:70%;" type="number" id="eebsmmin" size="12" onkeydown="return event.keyCode !== 69" maxlength="3" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"/></div>
                        <div id="diveebsmmax" style="margin-top:2px; display:flex; line-height:40px; vertical-align: middle;"><div align=right style="width:30%;">{{Valeur maxi}}:&nbsp;</div><input style="width:70%;" type="number" id="eebsmmax" size="12"  onkeydown="return event.keyCode !== 69" maxlength="3" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"/></div>
                        <div id="diveebsmval" style="margin-top:2px; display:flex; line-height:40px; vertical-align: middle;"><div align=right style="width:30%;">{{Valeur}}:&nbsp;</div><input style="width:70%;" type="number" id="eebsmval" size="12"  onkeydown="return event.keyCode !== 69" maxlength="3" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"/></div>
                        <div id="diveebsmunit" style="margin-top:2px; display:flex; line-height:40px; vertical-align: middle;"><div align=right style="width:30%;">{{Unité}}:&nbsp;</div><input style="width:70%;" type="text" id="eebsmunit" size="12" /></div>
                        <div id="diveebsmlist" style="margin-top:2px; display:flex; line-height:40px; vertical-align: middle;"><div align=right style="width:30%;">{{Contenu}}:&nbsp;</div><input placeholder="{{Séparer le contenu de la liste par | (Item1|Item2)}}" style="width:70%;" type="text" id="eebsmlist" size="12" /></div>
                        
                      
                      </div>
                    
                      <div id="eebsmbuttonajouter" class="eebsmbutton" style="background-color:var(--bt-success-color); width:95%; margin-top:10px;">{{Ajouter}}</div>
                      <div id="eebsmboutonenvoyer" class="eebsmbutton" style="background-color:var(--al-warning-color); width:95%; margin-top:10px;">{{Envoyer la mise à jour au module}}</div> 
                      <table id="eebsmtable" class="eebsminputzone">
                        <thead>
                          <tr>
                            <th class="eebsmcentre" width="15%">{{Type}}</th>
                            <th class="eebsmcentre" width="15%">{{Emp.}}</th>
                            <th class="eebsmcentre" width="30%">{{Nom}}</th>
                            <th class="eebsmcentre" width="10%">{{Unité}}</th>
                            <th class="eebsmcentre" width="8%">{{Mini}}</th>
                            <th class="eebsmcentre" width="8%">{{Maxi}}</th>
                            <th class="eebsmcentre" width="8%">{{Valeur}}</th>
                            <th class="eebsmcentre" width="6%"></th>

                          </tr>
                        </thead>
                        <tbody>
                          <tr>
                          </tr>
                        </tbody>
                      </table>
                  </div>
                          
<div class="row row-overflow">
	<!-- Page d'accueil du plugin -->
	<div class="col-xs-12 eqLogicThumbnailDisplay">
		<div class="row">
			<div class="col-sm-10">
				<legend><i class="fas fa-cog"></i> {{Gestion}}</legend>
				<!-- Boutons de gestion du plugin -->
				<div class="eqLogicThumbnailContainer">
					<div class="cursor eqLogicAction logoPrimary" data-action="add">
						<i class="fas fa-plus-circle"></i>
						<br>
						<span>{{Ajouter}}</span>
					</div>
                    <div class="cursor eqLogicAction logoSecondary" id="bt_addeeBsmlvgl">
						<i class='fas fa-pen '></i>
						<br>
						<span>{{Programmer un écran ESP32}}</span>
					</div>
					<div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
						<i class="fas fa-wrench"></i>
						<br>
						<span>{{Configuration}}</span>
					</div>
				</div>
			</div>
			<?php
			// à conserver
			// sera afficher uniquement si l'utilisateur est en version 4.4 ou supérieur
			$jeedomVersion  = jeedom::version() ?? '0';
			$displayInfoValue = version_compare($jeedomVersion, '4.4.0', '>=');
			if ($displayInfoValue) {
			?>
				<div class="col-sm-2">
					<legend><i class=" fas fa-comments"></i> {{Community}}</legend>
					<div class="eqLogicThumbnailContainer">
						<div class="cursor eqLogicAction logoSecondary" data-action="createCommunityPost">
							<i class="fas fa-ambulance"></i>
							<br>
							<span style="color:var(--txt-color)">{{Créer un post Community}}</span>
						</div>
					</div>
				</div>
			<?php
			}
			?>
		</div>
		<legend><i class="fas fa-table"></i> {{Mes équipements}}</legend>
		<?php
		if (count($eqLogics) == 0) {
			echo '<br><div class="text-center" style="font-size:1.2em;font-weight:bold;">{{Aucun équipement Template trouvé, cliquer sur "Ajouter" pour commencer}}</div>';
		} else {
			// Champ de recherche
			echo '<div class="input-group" style="margin:5px;">';
			echo '<input class="form-control roundedLeft" placeholder="{{Rechercher}}" id="in_searchEqlogic">';
			echo '<div class="input-group-btn">';
			echo '<a id="bt_resetSearch" class="btn" style="width:30px"><i class="fas fa-times"></i></a>';
			echo '<a class="btn roundedRight hidden" id="bt_pluginDisplayAsTable" data-coreSupport="1" data-state="0"><i class="fas fa-grip-lines"></i></a>';
			echo '</div>';
			echo '</div>';
			// Liste des équipements du plugin
			echo '<div class="eqLogicThumbnailContainer">';
			foreach ($eqLogics as $eqLogic) {
				$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
				echo '<div class="eqLogicDisplayCard cursor ' . $opacity . '" data-eqLogic_id="' . $eqLogic->getId() . '">';
				echo '<img src="' . $eqLogic->getImage() . '"/>';
				echo '<br>';
				echo '<b><span class="name">' . $eqLogic->getHumanName(true, true) . '</span></b>';
				echo '<span style="font-size: 12px;">'.$eqLogic->getConfiguration('adresseip'). '</span>';
				
				echo '<span class="hiddenAsCard displayTableRight hidden">';
				echo ($eqLogic->getIsVisible() == 1) ? '<i class="fas fa-eye" title="{{Equipement visible}}"></i>' : '<i class="fas fa-eye-slash" title="{{Equipement non visible}}"></i>';
				echo '</span>';
				echo '</div>';
              	
              $type_cmd = $eqLogic->getConfiguration('cmd_type');
			}
			echo '</div>';
		}
		?>
          
          
	</div> <!-- /.eqLogicThumbnailDisplay -->

	<!-- Page de présentation de l'équipement -->
	<div class="col-xs-12 eqLogic" style="display: none;">
		<!-- barre de gestion de l'équipement -->
		<div class="input-group pull-right" style="display:inline-flex;">
			<span class="input-group-btn">
				<!-- Les balises <a></a> sont volontairement fermées à la ligne suivante pour éviter les espaces entre les boutons. Ne pas modifier -->
				<a class="btn btn-sm btn-default eqLogicAction roundedLeft" data-action="configure"><i class="fas fa-cogs"></i><span class="hidden-xs"> {{Configuration avancée}}</span>
				</a><a class="btn btn-sm btn-default eqLogicAction" data-action="copy"><i class="fas fa-copy"></i><span class="hidden-xs"> {{Dupliquer}}</span>
				</a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}
				</a><a class="btn btn-sm btn-danger eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}
				</a>
			</span>
		</div>
		<!-- Onglets -->
		<ul class="nav nav-tabs" role="tablist">
			<li id="eebsm_retour" role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fas fa-arrow-circle-left"></i></a></li>
			<li id="eebsm_equipement" role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Equipement}}</a></li>
			<li id="eebsm_commandes" role="presentation" ><a href="#commandtab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-list"></i> {{Commandes}}</a></li>
		</ul>
		<div class="tab-content">
			<!-- Onglet de configuration de l'équipement -->
			<div role="tabpanel" class="tab-pane active" id="eqlogictab">
				<!-- Partie gauche de l'onglet "Equipements" -->
				<!-- Paramètres généraux et spécifiques de l'équipement -->
				<form class="form-horizontal">
					<fieldset>
						<div class="col-lg-6">
							<legend><i class="fas fa-wrench"></i> {{Paramètres généraux}}</legend>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Nom de l'équipement}}</label>
								<div class="col-sm-6">
									<input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display:none;">
									<input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement}}">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Objet parent}}</label>
								<div class="col-sm-6">
									<select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
										<option value="">{{Aucun}}</option>
										<?php
										$options = '';
										foreach ((jeeObject::buildTree(null, false)) as $object) {
											$options .= '<option value="' . $object->getId() . '">' . str_repeat('&nbsp;&nbsp;', $object->getConfiguration('parentNumber')) . $object->getName() . '</option>';
										}
										echo $options;
										?>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Catégorie}}</label>
								<div class="col-sm-6">
									<?php
									foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
										echo '<label class="checkbox-inline">';
										echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" >' . $value['name'];
										echo '</label>';
									}
									?>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Options}}</label>
								<div class="col-sm-6">
									<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked>{{Activer}}</label>
									<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked>{{Visible}}</label>
								</div>
							</div>

							<legend><i class="fas fa-cogs"></i> {{Paramètres spécifiques}}</legend>
                                      
                            
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Adresse IP}}
									<sup><i class="fas fa-question-circle tooltips" title="{{Renseignez l'adresse IP de l'ESP32}}"></i></sup>
								</label>
								<div class="col-sm-6">
									<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="adresseip" placeholder="{{Adresse IP}}">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label"> {{Mot de passe}}
									<sup><i class="fas fa-question-circle tooltips" title="{{Renseignez le mot de passe}}"></i></sup>
								</label>
								<div class="col-sm-6">
									<input type="text" class="eqLogicAttr form-control inputPassword" data-l1key="configuration" data-l2key="password">
								</div>
							</div>
                                      
                            <div class="form-group">
								<label class="col-sm-4 control-label"> {{Référence du module}}
									<sup><i class="fas fa-question-circle tooltips" title="{{La référence sera complétée automatiquement si elle a été paramétrée sur l'ESP32}}"></i></sup>
								</label>
								<div class="col-sm-6">
									<input type="text" disabled class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="module">
								</div>
							</div>
                                      
                            <div class="form-group">
								<label class="col-sm-4 control-label"> {{Ajouter les widgets eebsm}}
									<sup><i class="fas fa-question-circle tooltips" title="{{Si la case est cochée, les widgets eeBudServerManager seront ajoutés automatiquement}}"></i></sup>
								</label>
								<div class="col-sm-6">
									<input type="checkbox" value="1" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="widgets" checked>
								</div>
							</div>
                            
                            <div class="form-group">
								<label class="col-sm-4 control-label"> {{Pinguer et rafraîchir toutes les 15 minutes}}
									<sup><i class="fas fa-question-circle tooltips" title="{{Si la case est cochée, un ping et un rafraichissement seront effectués toutes les 15 minutes}}"></i></sup>
								</label>
								<div class="col-sm-6">
									<input type="checkbox" value="1" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="ping" checked>
								</div>
							</div>
                                      
                                      
                                      
                         </div>           
                                      
							

						<!-- Partie droite de l'onglet "Équipement" -->
						<!-- Affiche un champ de commentaire par défaut mais vous pouvez y mettre ce que vous voulez -->
						<div class="col-lg-6">
							<legend><i class="fas fa-info"></i> {{Informations}}</legend>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Description}}</label>
								<div class="col-sm-6">
									<textarea class="form-control eqLogicAttr autogrow" data-l1key="comment"></textarea>
								</div>
							</div>
                            <legend><i class="fas fa-at"></i> {{URL de retour}}</legend>
							<div class="form-group">
								<div class="alert alert-info col-xs-10 col-xs-offset-1 text-center callback">
									<span>
										<?php echo network::getNetworkAccess('external') . '/core/api/jeeApi.php?plugin=eebsm&type=event&apikey=' . jeedom::getApiKey($plugin->getId()) . '&id=#cmd_id# (&value=#value#)';
										?>
									</span>
								</div>
							</div>
						</div>
					</fieldset>
				</form>
			</div><!-- /.tabpanel #eqlogictab-->

			<!-- Onglet des commandes équipement -->
            
			<div role="tabpanel" class="tab-pane" id="commandtab">
				<div class="input-group pull-right" style="display:inline-flex;margin-top:5px;">					
				</div>
				<br>
                <p style="color : #4894E1;">{{Vous pouvez réorganiser les lignes en cliquant sur l'entête des colonnes}}</p>   <!-- ' -->                
				<div class="table-responsive">
					<table id="table_cmd" class="sortable table table-bordered table-condensed">
						<thead>
							<tr>
								<th class="sorttable_numeric" style="cursor : pointer; width:70px;">
                                   <button>
                                      ID
                                      <span aria-hidden="true"></span>
                                   </button>
                                </th>
                                      
                                <th style="cursor : pointer; width:200px;">
                                   <button>
                                      Nom
                                      <span aria-hidden="true"></span>
                                   </button>
                                </th>
                                      
                                <th class="sorttable_numeric" style="cursor : pointer; width:80px;">
                                   <button>
                                      eeBsm
                                      <span aria-hidden="true"></span>
                                   </button>
                                </th>
                                      
                                <th style="cursor : pointer; width:80px;">
                                   <button>
                                      Unités
                                      <span aria-hidden="true"></span>
                                   </button>
                                </th>
                                
                                <th class="sorttable_numeric" style="cursor : pointer; width:100px;">
                                   <button>
                                      Afficher
                                      <span aria-hidden="true"></span>
                                   </button>
                                </th>
                                
                                <th class="sorttable_numeric" style="cursor : pointer; width:100px;">
                                   <button>
                                      Historiser
                                      <span aria-hidden="true"></span>
                                   </button>
                                </th>
                                
                                <th style="cursor : pointer; width:150px;">
                                   <button>
                                      Type
                                      <span aria-hidden="true"></span>
                                   </button>
                                </th>
                                
                                <th style="cursor : pointer;">
                                   <button>
                                      Paramètres
                                      <span aria-hidden="true"></span>
                                   </button>
                                </th>
                  				
                                <th class="sorttable_numeric" style="cursor : pointer;">
                                   <button>
                                      Etat
                                      <span aria-hidden="true"></span>
                                   </button>
                                </th>
                                
                                <th style="cursor : pointer; width:90px;">
                                   <button>
                                      {{Actions}}
                                      <span aria-hidden="true"></span>
                                   </button>
                                </th>
                                
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>
			</div><!-- /.tabpanel #commandtab-->
			
		</div><!-- /.tab-content -->
	</div><!-- /.eqLogic -->
</div><!-- /.row row-overflow -->

<!-- Inclusion du fichier javascript du plugin (dossier, nom_du_fichier, extension_du_fichier, id_du_plugin) -->
<?php include_file('desktop', 'eebsm', 'js', 'eebsm'); ?>
<?php include_file('desktop', 'eebsm', 'css', 'eebsm'); ?>  
<?php include_file('desktop', 'sort', 'js', 'eebsm'); ?>
<!-- Inclusion du fichier javascript du core - NE PAS MODIFIER NI SUPPRIMER -->
<?php include_file('core', 'plugin.template', 'js'); ?>