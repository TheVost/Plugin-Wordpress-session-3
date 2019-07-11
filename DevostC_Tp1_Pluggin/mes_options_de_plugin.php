<?php
/*
* Description: Options du plugins de gestion d'événements pour le TP1 wordpress
* Author: Cédric Devost
* Date:   2019-03-15
*/

// Ajouter une entrée pour les paramètres du plugin dans le menu d’administration
add_action( 'admin_menu', 'DevostC_create_menu' );

function DevostC_create_menu() {
     //créer le menu
     add_menu_page( 'Page du Plugin de Gestion d\'événements ', 'Gestion d\'événements', 'manage_options', 'DevostC_main_menu', 'DevostC_settings_page', plugins_url( 'faisal.png', __FILE__ ) );
    
    //permettre de modifier le CSS de la page Admin
    add_action('admin_head', 'customForm');
    
     // enregistrer les paramètres : appel à la fonction d'enregistrement
    add_action( 'admin_init', 'DevostC_register_settings' );
}

//fonction qui ajoute du CSS dans le head de la page admin
function customForm() {
  echo 
      '<style>
            #cedro{
            width: 400px;
            font-size: 1.3em;
            text-align: center;
            }
            
            #cedro div{
            width: 150px;
            margin: 0 auto;
            }
            
            #cedro div input{
            float: right;
            position: relative;
            top: 7px;
            }
            
            #cedro div label{
            float: left;
            }
        </style>'
      ;
}

//enregistrer les paramètres
function DevostC_register_settings() {
     register_setting( 'DevostC-settings-group', 'DevostC_options',
     'DevostC_sanitize_options' );
}

// La liste des options sous forme de tableau
function DevostC_sanitize_options( $input ) {
    $input['option_salle'] = sanitize_text_field( $input['option_salle'] );
    $input['option_responsable'] = sanitize_text_field( $input['option_responsable'] );
    
    //autre forme de sanitize pour les checkbox
    if (isset($input['option_createAbonne'])) {$input['option_createAbonne'] = filter_var($input['option_createAbonne'], FILTER_SANITIZE_STRING);}
    if (isset($input['option_createContribut'])) {$input['option_createContribut'] = filter_var($input['option_createContribut'], FILTER_SANITIZE_STRING);}
    if (isset($input['option_createAuthor'])) {$input['option_createAuthor'] = filter_var($input['option_createAuthor'], FILTER_SANITIZE_STRING);}
    if (isset($input['option_createEdit'])) {$input['option_createEdit'] = filter_var($input['option_createEdit'], FILTER_SANITIZE_STRING);}
    if (isset($input['option_listeAbonne'])) {$input['option_listeAbonne'] = filter_var($input['option_listeAbonne'], FILTER_SANITIZE_STRING);}
    if (isset($input['option_listeContribut'])) {$input['option_listeContribut'] = filter_var($input['option_listeContribut'], FILTER_SANITIZE_STRING);}
    if (isset($input['option_listeAuthor'])) {$input['option_listeAuthor'] = filter_var($input['option_listeAuthor'], FILTER_SANITIZE_STRING);}
    if (isset($input['option_listeEdit'])) {$input['option_listeEdit'] = filter_var($input['option_listeEdit'], FILTER_SANITIZE_STRING);}

    return $input;
}

// La page des paramètres
function DevostC_settings_page() {
    
?>
<div class="wrap">
    <h2>Options de Gestion d'événements </h2><br />
    
    <form id="cedro" method="post" action="options.php" >
        
        <?php 
        settings_fields( 'DevostC-settings-group' );
        $devostC_options = get_option( 'DevostC_options' ); 
        ?>
        
        <!-- Valide si les champs ont été rempli, si oui, la valeur est déjà dans le text box -->
        <label>Salle Utilisée</label>
        <input type="text" name="DevostC_options[option_salle]" value="<?php if(isset(get_option('DevostC_options')['option_salle']) && get_option('DevostC_options')['option_salle'] != ""){echo get_option('DevostC_options')['option_salle']; }?>"/> <br>
        
        <label>Responsable</label>
        <input type="text" name="DevostC_options[option_responsable]" value="<?php if(isset(get_option('DevostC_options')['option_responsable']) && get_option('DevostC_options')['option_responsable'] != ""){echo get_option('DevostC_options')['option_responsable']; }?>"/>

        <h3>Permissions</h3>

        <h4>Créer événements</h4>
        
        <!-- Valide si les champs ont été coché, si oui, les checkbox correspondant seront cochés-->
        <div>
            <label>Abonné : </label><input type="checkbox" name="DevostC_options[option_createAbonne]" <?php if(isset(get_option('DevostC_options')['option_createAbonne'])){echo "checked=checked"; }?>/><br />
            
            <label>Contributeur : </label><input type="checkbox" name="DevostC_options[option_createContribut]" <?php if(isset(get_option('DevostC_options')['option_createContribut'])){echo "checked=checked"; }?>/><br />
            
            <label>Auteur : </label><input type="checkbox" name="DevostC_options[option_createAuthor]" <?php if(isset(get_option('DevostC_options')['option_createAuthor'])){echo "checked=checked"; }?>/><br />
            
            <label>Éditeur : </label><input type="checkbox" name="DevostC_options[option_createEdit]" <?php if(isset(get_option('DevostC_options')['option_createEdit'])){echo "checked=checked"; }?>/><br />
        </div>
        
        <h4>Lister inscrits</h4>
        
        <!-- Valide si les champs ont été coché, si oui, les checkbox correspondant seront cochés-->
        <div>
            <label>Abonné : </label><input type="checkbox" name="DevostC_options[option_listeAbonne]" <?php if(isset(get_option('DevostC_options')['option_listeAbonne'])){echo "checked=checked"; }?>/><br />
            
            <label>Contributeur : </label><input type="checkbox" name="DevostC_options[option_listeContribut]" <?php if(isset(get_option('DevostC_options')['option_listeContribut'])){echo "checked=checked"; }?>/><br />
            
            <label>Auteur : </label><input type="checkbox" name="DevostC_options[option_listeAuthor]" <?php if(isset(get_option('DevostC_options')['option_listeAuthor'])){echo "checked=checked"; }?>/><br />
            
            <label>Éditeur : </label><input type="checkbox" name="DevostC_options[option_listeEdit]" <?php if(isset(get_option('DevostC_options')['option_listeEdit'])){echo "checked=checked"; }?>/><br /><br />            
        </div>
        
        <input type="submit" class="button-primary" value="Appliquer" />

    </form>
</div>
<?php
 }
 ?>