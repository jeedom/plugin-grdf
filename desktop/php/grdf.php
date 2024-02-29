<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
$plugin = plugin::byId('grdf');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
?>
<div class="row row-overflow">
	<div class="col-xs-12 eqLogicThumbnailDisplay">
		<legend><i class="fas fa-cog"></i> {{Gestion}}</legend>
		<div class="eqLogicThumbnailContainer">
			<div class="cursor eqLogicAction logoPrimary" data-action="add">
				<i class="fas fa-plus-circle"></i>
				<br>
				<span>{{Ajouter}}</span>
			</div>
			<div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
				<i class="fas fa-wrench"></i>
				<br>
				<span>{{Configuration}}</span>
			</div>
		</div>
		<legend><i class="icon kiko-gas"></i> {{Mes compteurs}}</legend>
		<?php
		if (count($eqLogics) == 0) {
			echo '<br><div class="text-center" style="font-size:1.2em;font-weight:bold;">{{Aucun compteur GRDF trouvé, cliquer sur "Ajouter" pour commencer}}</div>';
		} else {
			echo '<div class="input-group" style="margin:5px;">';
			echo '<input class="form-control roundedLeft" placeholder="{{Rechercher}}" id="in_searchEqlogic">';
			echo '<div class="input-group-btn">';
			echo '<a id="bt_resetSearch" class="btn" style="width:30px"><i class="fas fa-times"></i></a>';
			echo '<a class="btn roundedRight hidden" id="bt_pluginDisplayAsTable" data-coreSupport="1" data-state="0"><i class="fas fa-grip-lines"></i></a>';
			echo '</div>';
			echo '</div>';
			echo '<div class="eqLogicThumbnailContainer">';
			foreach ($eqLogics as $eqLogic) {
				$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
				echo '<div class="eqLogicDisplayCard cursor ' . $opacity . '" data-eqLogic_id="' . $eqLogic->getId() . '">';
				echo '<img src="' . $plugin->getPathImgIcon() . '">';
				echo '<br>';
				echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
				echo '<span class="hiddenAsCard displayTableRight hidden">';
				echo ($eqLogic->getIsVisible() == 1) ? '' : '<i class="fas fa-eye-slash" title="{{Equipement non visible}}"></i> ';
				echo ($eqLogic->getConfiguration('pce_id', '') != '') ? '<span class="label label-info">' . $eqLogic->getConfiguration('pce_id') . '</span>' : '';
				echo '</span>';
				echo '</div>';
			}
			echo '</div>';
		}
		?>
	</div>

	<div class="col-xs-12 eqLogic" style="display: none;">
		<div class="input-group pull-right" style="display:inline-flex">
			<span class="input-group-btn">
				<a class="btn btn-sm btn-default eqLogicAction roundedLeft" data-action="configure"><i class="fas fa-cogs"></i><span class="hidden-xs"> {{Configuration avancée}}</span>
				</a><a class="btn btn-sm btn-default eqLogicAction" data-action="copy"><i class="fas fa-copy"></i><span class="hidden-xs"> {{Dupliquer}}</span>
				</a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}
				</a><a class="btn btn-danger btn-sm eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
			</span>
		</div>
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fas fa-arrow-circle-left"></i></a></li>
			<li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Equipement}}</a></li>
			<li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-list-alt"></i> {{Commandes}}</a></li>
		</ul>
		<div class="tab-content">
			<div role="tabpanel" class="tab-pane active" id="eqlogictab">
				<form class="form-horizontal">
					<fieldset>
						<div class="col-lg-6">
							<legend><i class="fas fa-wrench"></i> {{Paramètres généraux}}</legend>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Nom du compteur}}</label>
								<div class="col-sm-6">
									<input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;">
									<input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom du compteur}}">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Objet parent}}</label>
								<div class="col-sm-6">
									<select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
										<option value="">{{Aucun}}</option>
										<?php $options = '';
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
								<label class="col-sm-4 control-label">{{Identifiant PCE}}
									<sup><i class="fas fa-question-circle tooltips" title="{{Indiquer le numéro d'identification du PCE (14 chiffres ou GI + 6 chiffres)}}"></i></sup>
								</label>
								<div class="col-sm-6">
									<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="pce_id" placeholder="{{PCE (14 chiffres ou GI + 6 chiffres)}}">
								</div>
							</div>
							<div class="form-group show-MM show-JJ" style="display:none;">
								<label class="col-sm-4 control-label"> {{Type de mesure}}
									<sup><i class="fas fa-question-circle tooltips" title="{{Indiquer le type de mesure à récupérer (consommation, injection ou les deux)}}"></i></sup>
								</label>
								<div class="col-sm-6">
									<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="measure_type">
										<option value="consos">{{Consommation}}</option>
										<option value="injections">{{Injection}}</option>
										<option value="both">{{Consommation}} + {{Injection}}</option>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Coefficient de conversion}}
									<sup><i class="fas fa-question-circle tooltips" title="{{Enregistrer le coefficient de conversion quotidien}}"></i></sup>
								</label>
								<div class="col-sm-6">
									<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="include_coef">{{Enregistrer}}</label>
								</div>
							</div>
						</div>

						<div class="col-lg-6">
							<legend><i class="fas fa-user-shield"></i> {{Autorisation d'accès}}</legend>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Etat de l'autorisation}}
									<sup><i class="fas fa-question-circle tooltips" title="{{Etat de l'autorisation d'accès à l'API GRDF}}"></i></sup>
								</label>
								<div class="col-sm-7">
									<span class="eqLogicAttr" data-l1key="configuration" data-l2key="access_rights" data-l3key="etat_droit_acces"></span>
									{{du}}
									<span class="eqLogicAttr" data-l1key="configuration" data-l2key="access_rights" data-l3key="date_debut_droit_acces"></span>
									{{au}}
									<span class="eqLogicAttr" data-l1key="configuration" data-l2key="access_rights" data-l3key="date_fin_droit_acces"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Données contractuelles}}
									<sup><i class="fas fa-question-circle tooltips" title="{{Accès aux données contractuelles ? (facultatif)}}"></i></sup>
								</label>
								<div class="col-sm-7">
									<span class="eqLogicAttr" data-l1key="configuration" data-l2key="access_rights" data-l3key="perim_donnees_contractuelles"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Données techniques}}
									<sup><i class="fas fa-question-circle tooltips" title="{{Accès aux données techniques ? (obligatoire)}}"></i></sup>
								</label>
								<div class="col-sm-7">
									<span class="eqLogicAttr" data-l1key="configuration" data-l2key="access_rights" data-l3key="perim_donnees_techniques"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Données publiées}}
									<sup><i class="fas fa-question-circle tooltips" title="{{Accès aux données publiées ?}}"></i></sup>
								</label>
								<div class="col-sm-7">
									<span class="eqLogicAttr" data-l1key="configuration" data-l2key="access_rights" data-l3key="perim_donnees_publiees"></span>
								</div>
							</div>
							<div class="form-group show-1M show-MM" style="display:none;">
								<label class="col-sm-4 control-label">{{Données informatives}}
									<sup><i class="fas fa-question-circle tooltips" title="{{Accès aux données informatives ?}}"></i></sup>
								</label>
								<div class="col-sm-7">
									<span class="eqLogicAttr" data-l1key="configuration" data-l2key="access_rights" data-l3key="perim_donnees_informatives"></span>
								</div>
							</div>
							<div class="form-group hide-injections">
								<label class="col-sm-4 control-label">{{Période de consommation}}
									<sup><i class="fas fa-question-circle tooltips" title="{{Période de consultation autorisée pour les données de consommation}}"></i></sup>
								</label>
								<div class="col-sm-7">
									{{du}}
									<span class="eqLogicAttr" data-l1key="configuration" data-l2key="access_rights" data-l3key="perim_donnees_conso_debut"></span>
									{{au}}
									<span class="eqLogicAttr" data-l1key="configuration" data-l2key="access_rights" data-l3key="perim_donnees_conso_fin"></span>
								</div>
							</div>
							<div class="form-group hide-consos">
								<label class="col-sm-4 control-label">{{Période d'injection}}
									<sup><i class="fas fa-question-circle tooltips" title="{{Période de consultation autorisée pour les données d'injection}}"></i></sup>
								</label>
								<div class="col-sm-7">
									{{du}}
									<span class="eqLogicAttr" data-l1key="configuration" data-l2key="access_rights" data-l3key="perim_donnees_inj_debut"></span>
									{{au}}
									<span class="eqLogicAttr" data-l1key="configuration" data-l2key="access_rights" data-l3key="perim_donnees_inj_fin"></span>
								</div>
							</div>

							<legend><i class="fas fa-info"></i> {{Informations}}</legend>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Type de compteur}}</label>
								<div class="col-sm-7">
									<span class="eqLogicAttr label label-info" data-l1key="configuration" data-l2key="reading_frequency"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Dernière publication}}
									<sup><i class="fas fa-question-circle tooltips" title="{{Date de dernière publication des données}}"></i></sup>
								</label>
								<div class="col-sm-7">
									<span class="eqLogicAttr label label-info" data-l1key="configuration" data-l2key="reading_date"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Fréquence de relève}}
									<sup><i class="fas fa-question-circle tooltips" title="{{Fréquence de mise à disposition des données}}"></i></sup>
								</label>
								<div class="col-sm-7">
									<div class="alert alert-info">
										<ul class="show-6M" style="display:none;">
											<li>
												<strong>{{Données semestrielles (publiées)}}</strong>
												<div>{{Tous les 6 mois de J+2 à J+3 après la relève à pied}}</div>
											</li>
										</ul>
										<ul class="show-1M" style="display:none;">
											<li>
												<strong>{{Données mensuelles mois M-1 (publiées)}}</strong>
												<div>{{Tous les mois de J+2 à J+3 après la date de publication}}</div>
											</li>
											<li>
												<strong>{{Données quotidiennes (informatives)}}</strong>
												<ul>
													<li>
														<strong>{{Estimées}}</strong>
														<div>{{Tous les jours de J+1 à J+3}}</div>
													</li>
													<li>
														<strong>{{Définitives mois M-1}}</strong>
														<div>{{Tous les mois de J+2 à J+3 après la date de publication}}</div>
													</li>
												</ul>
											</li>
										</ul>
										<ul class="show-MM" style="display:none;">
											<li>
												<strong>{{Données mensuelles (publiées)}}</strong>
												<div>{{Tous les mois entre J+2 après la date de publication et le 7ème JO du mois M}}</div>
											</li>
											<li>
												<strong>{{Données quotidiennes mois M-1 (informatives)}}</strong>
												<div>{{Tous les mois entre le 10 et le 20 du mois M}}</div>
											</li>
										</ul>
										<ul class="show-JJ" style="display:none;">
											<li>
												<strong>{{Données quotidiennes (publiées)}}</strong>
												<ul>
													<li>
														<strong>{{Estimées}}</strong>
														<div>{{Tous les jours de J+1 à J+2}}</div>
													</li>
													<li>
														<strong>{{Définitives mois M-1}}</strong>
														<div>{{Tous les mois entre le 1er et le 6ème JO du mois M}}</div>
													</li>
												</ul>
											</li>
										</ul>
									</div>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Description}}</label>
								<div class="col-sm-7">
									<textarea data-l1key="comment" class="form-control eqLogicAttr autogrow"></textarea>
								</div>
							</div>

						</div>

					</fieldset>
				</form>
			</div>

			<div role="tabpanel" class="tab-pane" id="commandtab">
				<br>
				<div class="table-responsive">
					<table id="table_cmd" class="table table-bordered table-condensed">
						<thead>
							<tr>
								<th class="hidden-xs" style="min-width:50px;width:70px;">ID</th>
								<th style="min-width:200px;width:300px;">{{Nom}}</th>
								<th>{{Etat}}</th>
								<th style="min-width:200px;width:250px;">{{Options}}</th>
								<th style="min-width:100px;width:250px;">{{Actions}}</th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>
			</div>

		</div>
	</div>
</div>

<?php include_file('core', 'plugin.template', 'js'); ?>
<?php include_file('desktop', 'grdf', 'js', 'grdf'); ?>
