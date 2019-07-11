<?php
/*
Plugin Name: Pluggin de gestion d'événements
Plugin URI: http://example.com/premier-plugin
Description: Pluggin permettnt de faire la gestion des événements pour notre site
Version: 1.0
Author: Cédric Devost
Author URI: http://example.com
License: GPLv2
*/

//inclus les options de mon plugins
include ("mes_options_de_plugin.php");

//fonction de modification du CSS pour certains forms dans les pages du plugin
function custom_CSS(){
    echo 
        "<style>
            #gros{
                font-size: 2em;
            }
        
            .hidden{
                display: none;
            }
            
            #creer{
                width: 100%;
                margin: 0 auto;
            }
            
            #retour{
                display: inline-block;
                width: 200px;
                line-heigth: 50px;
                border: 3px solid grey;
                text-align: center;
                font-size: 2em;
                background: f1f1f1;
            }
            
            #retour:hover{
                background: grey;
                color: blue;
            }
            
            td, th, tr{
                border: none;
                border-bottom : 1px solid black;
            }
            
            table{
                border: none;
            }
            
            #rouge{
                color: red;
            }
        </style>";
}

//fait appel à la fonction de changement du CSS 
add_action('wp_head', 'custom_CSS');

//---------------------------------------------------------------------------------------------PAGE INSCRIPTION
//fonction qui détermine le contenu de la page inscription
function event_page_inscription() {
    global $wpdb;

    if(isset($_GET['id'])){
        $id = $_GET['id'];
    }
    
    //permet de trouver l'Activité que l'on visualise pour l'utiliser plus loin
    $activite = $wpdb->get_row("SELECT *
                                FROM wp5evenements  
                                WHERE event_id =" . $id);
    
    ?>

    <h1>S'incrire à l'événement</h1>
    <h2>
        <!-- Inscrit le nom de l'Activité et la date-->
        <span id="rouge"><?php echo $activite->event_nom . "</span> en date du " . $activite->event_date_debut; ?>
    </h2>
    <form method="post" action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>">
        <lable>Nom Complet : </lable>
        <input type="text" name="nom" required><br>
        <lable>Courriel : </lable>
        <input type="text" name="email" required><br>
        <lable>Téléphone : </lable>
        <input type="text" name="tel" required><br>

        <!-- permet de garder en mémoire l'id de l'event même après rafraichissement de la page -->
        <input type="text" name="id" value="<?php echo $id; ?>" class="hidden">

        <input type="submit" name="inscrire" value="S'inscrire!">
    </form>
    <br>

    <a id="retour" href="evenements" title="Retour">Retour</a>

    <?php
}  

//fonction qui va permettre la saisie des informations dans la page inscription
function inscription_event() {
    global $wpdb;

    //détermine si on a cliqué sur le bouton
    if (isset( $_POST['inscrire'])){
     
        //valide si les champs sont remplis, si oui, affecte la valeur à une variable
        if(isset($_POST['nom'])){
            $nom = $_POST['nom'];
        }
        if(isset($_POST['email'])){
            $email = $_POST['email'];
        }
        if(isset($_POST['tel'])){
            $tel = $_POST['tel'];
        }
        
        //trouve les id de l'event
        $id = $_POST['id'];
         
        //Vérifie si l'usger est déjà inscrit ou non (un seul usager peut avoir le même adresse courriel pour un évéement donné)
        if(count($wpdb->get_results("SELECT ec_client_email FROM wp5event_client WHERE ec_client_email ='$email' AND ec_fk_event_id ='$id'")) == 1 ){
            echo "<h2>Vous êtes Déjà inscrits!</h2>"; 
        }
        else{
            $wpdb->query( $wpdb->prepare("INSERT INTO wp5event_client (ec_fk_event_id, ec_client_nom, ec_client_email, ec_client_tel)
            VALUES (%d, %s, %s, %s)", $id, $nom, $email, $tel));
            
            echo "<h2>Inscription effectuée avec succès!</h2>";    
        }
    }
    else{
        if(isset($_GET['id'])){
            $id = $_GET['id'];
        }
    }
} 

//fonction qui crée le shortcode pour la création de cette page
function event_inscrire_shortcode() {
    ob_start(); 
    
    event_page_inscription();
    inscription_event();

    return ob_get_clean(); 
} 

// crée un shortcode pour insérer le contenu de la page
add_shortcode( 'inscrire', 'event_inscrire_shortcode' );

//fonction qui crée la page de gestion inscription en prenant en compte le contenu élaboré plus haut
function creer_page_inscription(){
    $client_post = array(
    'post_title' => "Inscription",
    'post_content' => '[inscrire]',
    'post_type' => 'page',
    'post_status' => 'publish',
    'comment_status' => 'closed',
    );
    
    //insère la page dans le site
    wp_insert_post($client_post);
}

//------------------------------------------------------------------------------------------------PAGE CRÉER
//fonction qui détermine le contenu de la page creer
function event_page_creer() {

    ?>
    <h1>Créer un Événement</h1>
    <form id="creer" method="post" action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>">
        <div>
            <label>Nom : </label>
            <input type="text" name="nom" required><br>
            <label>Description : </label>
            <input type="text" name="desc" required><br>
            <label>Type : </label>
            <select name="categorie">
                <option value="1">Sportif</option>
                <option value="2">Sociale</option>
                <option value="3">Politique</option>
                <option value="4">Autre</option>
            </select><br>
            <br>
            <label>Fréquence : </label>
            <select name="frequence">
                <option value="3">Une seule fois</option>
                <option value="1">Hebdo</option>
                <option value="2">Mensuelle</option>
            </select>
            <br>
        </div>
        
        <div>
            <!-- Met par défault la date du jour comme date de début -->
            <label>Date de début</label>
            <input type="date" name="dateDebut" value="<?php echo date('Y-m-d'); ?>" required><br>
            <label>Date de fin</label>
            <input type="date" name="dateFin" required><br>
            <label>Responsable : </label>

            <!-- Met visible, mais non modifiable, les options que l'Administrateur va avoir désigné au préalable -->
            <input type="text" name="resp" value="<?php echo get_option('DevostC_options')['option_responsable']; ?>" disabled> (Assignée par l'administrateur)<br>
            <label>Salle Utilisée : </label>
            <input type="text" name="salle" value="<?php echo get_option('DevostC_options')['option_salle']; ?>" disabled> (Assignée par l'administrateur)<br>

            <!-- Pour rentrer l'autheur dand la base de donnée de façon transparente -->
            <input type="text" name="author" value="<?php echo wp_get_current_user()->display_name; ?>" class="hidden">
        </div>
        <input id="submit" type="submit" name="creer" value="Créer">
    </form>
    <br>

    <a id="retour" href="evenements" title="Retour">Retour</a>

    <?php
}  

//fonction permettant de faire la saisie de ce qu'il y a dans les champs de la page creer
function inserer_event() {
    
     if ( isset( $_POST['creer'] ) ) {

         $erreurs = "";
        //valide que les champs sont remplie et les sanatize
         if(isset($_POST["nom"])){
             $nom = sanitize_text_field( $_POST["nom"] );
         }
         if(isset($_POST["desc"])){
             $description = sanitize_text_field( $_POST["desc"] );
         }
         if(isset($_POST["categorie"])){
             $categorie = filter_var($_POST["categorie"], FILTER_SANITIZE_STRING);
         }
         if(isset($_POST["frequence"])){
             $frequence = filter_var($_POST["frequence"], FILTER_SANITIZE_STRING);
         }
         if(isset($_POST["dateDebut"])){
             $dateD = filter_var($_POST["dateDebut"], FILTER_SANITIZE_STRING);
         }
         
         //valide si la date de Fin a bien lieu après ou le même jour que la date de début
         if(isset($_POST["dateFin"])){
             if(strtotime($dateD) <= strtotime($_POST["dateFin"])){
                 $dateF = filter_var($_POST["dateFin"], FILTER_SANITIZE_STRING);
             }
             else{
                 $erreurs .= "<br><h2>Veuillez entrer une date de fin valide</h2>";
             }
         }
         
         //Si la salle a été déterminé dans les options, on la saisie, sinon ne laisse pas le champs vide dans la BD, saisie à voir...
         if(isset($_POST["salle"])){
             $salle = sanitize_text_field( $_POST["salle"] );
         }
         else{
             $salle = "À voir...";
         }
         if(isset($_POST["author"])){
             $author = sanitize_text_field( $_POST["author"] );
         }

        // Code de traitement du formulaire : insertion dans la BD dans la table evenement et celle event_periodicite
         global $wpdb;
         
         //vérification si les dates sont bonnes avant d'envoyer
         if($erreurs == ""){
             $wpdb->query( $wpdb->prepare("INSERT INTO wp5evenements
             (event_nom, event_date_debut, event_date_fin, event_categorie, event_lieu, event_auteur, event_description, event_periodicity)
             VALUES ( %s, %s, %s, %d, %s, %s, %s, %d )", $nom, $dateD, $dateF, $categorie, $salle, $author, $description, $frequence));

             $activiteId = $wpdb->get_row("SELECT event_id FROM wp5evenements WHERE event_nom ='$nom'"); 
 
             $wpdb->query( $wpdb->prepare("INSERT INTO wp5event_periodicite
             (ep_fk_periodicite_id)
             VALUES (%d)", $frequence));

             echo "<h2>Événement créé avec succès!</h2>";
         }
         else{
             echo $erreurs;
         }
     }
} 

//création du shortcode pour l'insertion du contenu de la page creer
function event_create_shortcode() {
    ob_start(); 
    
    event_page_creer();
    inserer_event();

    return ob_get_clean(); 
} 

// crée un shortcode pour insérer le formulaire
add_shortcode( 'create', 'event_create_shortcode' );

//fonction qui crée la page de gestion créer en fonction du contenu établi plus haut
function creer_page_creer(){
    $client_post = array(
    'post_title' => "Création d'un Événement",
    'post_content' => '[create]',
    'post_type' => 'page',
    'post_status' => 'publish',
    'comment_status' => 'closed',
    );
    
    //insère la page dans le site
    wp_insert_post($client_post);
}

//---------------------------------------------------------------------------------------------------PAGE ÉVÉNEMENTS
//fonction qui détermine le contenu de la page événements
function event_page_content() {
    global $wpdb;
    
    $aLeDroit = false;
    
    //instanciation des vatiables utioles plus loin
    $tableauCreer = array();
    $tableauLister = array();
    
    $table_name = $wpdb->prefix . "evenements";
    
    //permet de savoir la catégorie, par défaut le même à la première
    if(isset($_POST['type'])){
        $type = $_POST['type'];
        $date = $_POST['date'];
    }
    else{
        $type = 1;
    }
    
    //gère la date des événements à afficher
    if(isset($_GET['date'])){
        $date = $_GET['date'];
    }
    else{
        $date = date('Y-m-d');
    }
    
    //variable pour faire un echo plus loin, sans faire une requête avec des jonctions à la BD
    if($type == 1){
        $categorie = "Sportif";
    }
    elseif($type == 2){
        $categorie = "Social";
    }
    elseif($type == 3){
        $categorie = "Politique";
    }
    elseif($type == 4){
        $categorie = "Autre";
    }
    
    //sort les résultats pour une catégorie donnée et une date donnée
    $rows = $wpdb->get_results("SELECT * FROM $table_name WHERE event_categorie ='" . $type . "' AND event_date_debut ='" . $date . "'");
    
    //vérifie si l'usager est connecté ou non
    if (is_user_logged_in()){
        $user_meta = get_userdata(wp_get_current_user()->ID);
        $user_roles = $user_meta->roles;

        //met dans un tableau les valeurs des options du plugins qui ont été activées concernant les droits pour créer un événement
        if(isset(get_option('DevostC_options')['option_createAbonne'])){
            array_push($tableauCreer, "subscriber");
        }
        if(isset(get_option('DevostC_options')['option_createContribut'])){
            array_push($tableauCreer, "contributor");
        }
        if(isset(get_option('DevostC_options')['option_createEdit'])){
            array_push($tableauCreer, "editor");
        }
        if(isset(get_option('DevostC_options')['option_createAuthor'])){
            array_push($tableauCreer, "author");
        }
        
        //met dans un tableau les valeurs des options du plugins qui ont été activées concernant les droits pour lister les inscriptions
        if(isset(get_option('DevostC_options')['option_listeAbonne'])){
            array_push($tableauLister, "subscriber");
        }
        if(isset(get_option('DevostC_options')['option_listeContribut'])){
            array_push($tableauLister, "contributor");
        }
        if(isset(get_option('DevostC_options')['option_listeEdit'])){
            array_push($tableauLister, "editor");
        }
        if(isset(get_option('DevostC_options')['option_listeAuthor'])){
            array_push($tableauLister, "author");
        }
        
        //utilisation du tableau pour voir si l'usager courant à le droit de lister les inscriptions
        for($i = 0; $i < count($tableauLister); $i++){
                if(trim($user_roles[0]) == $tableauLister[$i]){
                    $aLeDroit = true;
            }
        }
        
        //utilisation du tableau pour voir si l'usager courant à le droit de créer un événement et ajout d'un lien pour le faire dans ce cas
        for($i = 0; $i < count($tableauCreer); $i++){
            if(trim($user_roles[0]) == $tableauCreer[$i]){
                echo "<a href='creation-dun-evenement' title='creer un événement'>Créer un événement</a><br>";
            }
        }
        
        //valide la même chose, mais pour l'admin
        if(trim($user_roles[0]) == 'administrator'){
            echo "<a href='creation-dun-evenement' title='creer un événement'>Créer un événement</a><br>";
            $aLeDroit = true;
        }
    }

    ?>
    <!-- Contenu de la page -->

    <h1>Événements</h1>
        <label id="gros">De type : <span id="rouge"><?php echo $categorie; ?></span></label>

    <!-- Permet de changer la catégorie listé -->
    <form method="post">
        <select name="type" onchange="submit()">
            <option>Choisir une catégorie svp</option>
            <option value="1">Sportif</option>
            <option value="2">Social</option>
            <option value="3">Politique</option>
            <option value="4">Autre</option>
        </select>
        <input type="hidden" name="date" value="<?php echo $date; ?>">
    </form>

    <label id="gros">En date du : </label>
    <form method="get">
        <input type="date" name="date" onchange="submit()" value="<?php echo $date; ?>">
    </form>

    <!-- Vérifie s'il y a des activitées a lister avec les critères entrés en paramètres -->
    <?php if(count($rows) != 0) : ?>

        <table>
            <tr>
                <th>Nom</th>
                <th>Description</th>
                <th>Début</th>
                <th>Fin</th>
                <th>Salle</th>
            </tr>

            <!-- remplissage dynamique en fonction de ce qu'il y a dans la BD -->
            <!-- J'ai choisi de n'Afficher que certains champs que je juge plus pertinent pour l'exercice, bien que tous ceux demandé dans le devis sont enregistrés à la créatino de l'événement et storé dans la BD  -->
            <?php foreach($rows as $row) :  ?>

                <tr>
                    <td>
                        <?php echo $row->event_nom; ?>
                    </td>
                    <td>
                        <?php echo $row->event_description; ?>
                    </td>
                    <td>
                        <?php echo $row->event_date_debut; ?>
                    </td>
                    <td>
                        <?php echo $row->event_date_fin; ?>
                    </td>
                    <td>
                        <?php echo $row->event_lieu; ?>
                    </td>

                    <td>

                        <!-- Vérifie que l'Activité n'est pas passée, si c'Est le cas, ne permet pas l'option de s'y inscrire -->
                        <?php if($row->event_date_debut >= date('Y-m-d')) : ?>

                            <!-- le liens change en fonction de l'Activité auquel il est rattaché -->
                            <a href="inscription?id=<?php echo $row->event_id; ?>" title="s'inscrire">Inscription</a></td>

                        <?php endif; ?>

                    <td>

                    <?php  
                    //le liens s'affiche si l'utilisateur a les accès et il change en fonction de l'Activité auquel il est rattaché
                    if($aLeDroit == true){
                        echo "<a href='liste-des-inscriptions?id=" . $row->event_id . "' title='Lister membres inscrits'>Lister Membres Inscrits</a><br>";
                    }

                    ?>
                    </td>
                </tr>

            <?php endforeach; ?>

        </table>

    <!-- Dans le cas où aucune activitée ne correspond à la requête -->
    <?php else : 

        echo "<h2>Aucune Activité à afficher!</h2>";

    endif;
}  

//création du shortcode permettant l'implémentation du contenu élaboré plus haut
function event_page_shortcode() {
    ob_start(); 
    
    event_page_content();

    return ob_get_clean(); 
} 

// crée un shortcode pour insérer le formulaire
add_shortcode( 'event', 'event_page_shortcode' );

//fonction qui crée la page de gestion des événements en fonction du contenu créé plus haut
function creer_page_event(){
    
    $client_post = array(
    'post_title' => "Événements",
    'post_content' => '[event]',
    'post_type' => 'page',
    'post_status' => 'publish',
    'comment_status' => 'closed',
    );
    
    //insère la page dans le site
    wp_insert_post($client_post);
}

//----------------------------------------------------------------------------------------------------PAGE LISTER
//fonction qui détermine le contenu de la page lister
function event_page_lister() {
    global $wpdb;
    
    $id = $_GET['id'];
    
    //détermine l'Activité pour l'utiliser plus loin
    $activite = $wpdb->get_row("SELECT *
                                FROM wp5evenements  
                                WHERE event_id =" . $id);
    
    //détermine les gens inscerits
    $rows = $wpdb->get_results("SELECT ec_client_nom
                                FROM wp5event_client
                                WHERE ec_fk_event_id =" . $id);
    
    ?>
    <h1>Inscrits à l'activitée :</h1>
    <h2>
        <span id="rouge"><?php echo $activite->event_nom . "</span> en date du " . $activite->event_date_debut; ?>
    </h2>

    <!-- vérifie s'il y a déjà des inscriptions ou non -->
    <?php if(count($rows) != 0) : ?>

        <ul>
            <!-- Liste les gens inscrits -->
            <?php foreach($rows as $row) : ?>

                <li>
                    <?php echo $row->ec_client_nom; ?>
                </li>

            <?php endforeach; ?>

        </ul>

    <!-- S'il n'y a pas d'inscription -->
    <?php else : 

        echo "<h3>Il n'y a personne d'inscrit pour le moment!</h3>";  

    endif; ?>

    <br>

    <a id="retour" href="evenements" title="Retour">Retour</a>

    <?php
}  

//création du shortcode pour implémenter le contenu élaboré plus haut
function event_lister_shortcode() {
    ob_start(); 
    
    event_page_lister();

    return ob_get_clean(); 
} 

// crée un shortcode pour insérer le formulaire
add_shortcode( 'lister', 'event_lister_shortcode' );

//fonction qui crée la page de gestion de liste en fonction du contenu créé plus haut
function creer_page_liste(){
    
    $client_post = array(
    'post_title' => "Liste des Inscriptions",
    'post_content' => '[lister]',
    'post_type' => 'page',
    'post_status' => 'publish',
    'comment_status' => 'closed',
    );
    
    //insère la page dans le site
    wp_insert_post($client_post);
}

//-----------------------------------------------------------------------------------------GESTION DE LA BD
//fonction pour créer la table evenements
function creerEvenement() {
    global $wpdb;

    $table_name = $wpdb->prefix . "evenements";
    
    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        event_id            int             NOT NULL AUTO_INCREMENT,
        event_nom           varchar(255)    NOT NULL,
        event_date_debut    varchar(255)    NOT NULL,
        event_date_fin      varchar(255)    NOT NULL,
        event_categorie     int     NOT NULL,
        event_lieu          varchar(255)    NOT NULL,
        event_auteur        varchar(255)     NOT NULL,
        event_description   varchar(255)    NOT NULL,
        event_periodicity    int     NOT NULL,
        PRIMARY KEY  (event_id)
    ) $charset;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}

//fonction pour créer la table categories
function creerCategorie() {
    global $wpdb;

    $table_name = $wpdb->prefix . "categories";
    
    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        categorie_id            int             NOT NULL AUTO_INCREMENT,
        categorie_nom           varchar(255)    NOT NULL,
        PRIMARY KEY  (categorie_id)
    ) $charset;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}

//fonction pour créer la table event_client
function creerEventClient() {
    global $wpdb;

    $table_name = $wpdb->prefix . "event_client";
    
    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        ec_inscription_id       int     UNSIGNED    NOT NULL AUTO_INCREMENT,
        ec_fk_event_id          int             NOT NULL,
        ec_client_nom           varchar(255)    NOT NULL,
        ec_client_email         varchar(255)    NOT NULL,
        ec_client_tel           varchar(255)    NOT NULL,
        PRIMARY KEY  (ec_inscription_id)
    ) $charset;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}

//fonction pour créer la table periodicites
function creerPeriodicite() {
    global $wpdb;

    $table_name = $wpdb->prefix . "periodicites";
    
    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        periodicite_id     int             NOT NULL AUTO_INCREMENT,
        periodicite_nom    varchar(255)    NOT NULL,
        PRIMARY KEY  (periodicite_id)
    ) $charset;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}

//fonction pour créer la table event_periodicite
function creerEventPeriodicite() {
    global $wpdb;

    $table_name = $wpdb->prefix . "event_periodicite";
    
    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        ep_fk_event_id           int    NOT NULL AUTO_INCREMENT,
        ep_fk_periodicite_id     int    NOT NULL,
        PRIMARY KEY  (ep_fk_event_id, ep_fk_periodicite_id)
    ) $charset;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}

//fonction de population automatique de certaines tables
function populerTables(){
    global $wpdb;
    $table_name = $wpdb->prefix . "periodicites";
    
    //insère ces valeurs dans la table periodicites
    $wpdb->insert($table_name, array('periodicite_nom' => 'Hebdo'));
    $wpdb->insert($table_name, array('periodicite_nom' => 'Mensuelle'));
    $wpdb->insert($table_name, array('periodicite_nom' => 'Quotidienne'));
    
    $table_name = $wpdb->prefix . "categories";
    
    //insère ces valeurs dans la table categories
    $wpdb->insert($table_name, array('categorie_nom' => 'Sportif'));
    $wpdb->insert($table_name, array('categorie_nom' => 'Social'));
    $wpdb->insert($table_name, array('categorie_nom' => 'Politique'));
    $wpdb->insert($table_name, array('categorie_nom' => 'Autre'));
}

//fonction qui ajoute la page Événement au menu de navigation principal du site
function ajouterEvenementMenu(){
    //trouve le menu que je veux modifier
    $locations = get_theme_mod( 'nav_menu_locations' );
    $menu = wp_get_nav_menu_object( reset( $locations ) ); 
    
    //contient les info du nouvel item de menu
    $menu_item_data = array(
        'menu-item-object-id' => get_page_by_title('Événements')->ID,
        'menu-item-parent-id' => 0,              
        'menu-item-position' => 0,               
        'menu-item-object' => 'page',
        'menu-item-type' => 'post_type',
        'menu-item-status' => 'publish'
    );
    
    //ajout de l'élément au menu
    wp_update_nav_menu_item( $menu->term_id, 0, $menu_item_data );
}

//pour lancer l'Activtion du pluggin
register_activation_hook( __FILE__, 'monPluggin_install' );

//-----------------------------------------------------------------------------------------------------CRÉATION DU PLUGIN ET DE SES COMPOSANTES
//ici qu'on crée les tables et pages
function monPluggin_install() {
    creerCategorie();
    creerEvenement();
    creerEventClient();
    creerPeriodicite();
    creerEventPeriodicite();
    populerTables();
    creer_page_event();
    creer_page_liste();
    creer_page_creer();
    creer_page_inscription();
    ajouterEvenementMenu();
}

//pour lancer la désactivation du pluggin
register_deactivation_hook( __FILE__, 'monPluggin_deactivate' );

//-----------------------------------------------------------------------------------------------------SUPPRESSION DU PLUGIN ET DE SES COMPOSANTES
//ici qu'on supprimer les tables et les pages
function monPluggin_deactivate() {
    global $wpdb;
    
    //-----------------------------------------------------------destruction des tables
    $table1= $wpdb->prefix . 'evenements';
    $sql = "DROP TABLE IF EXISTS $table1";
    $wpdb->query($sql);
    
    $table2= $wpdb->prefix . 'categories';
    $sql = "DROP TABLE IF EXISTS $table2";
    $wpdb->query($sql);
    
    $table3= $wpdb->prefix . 'event_client';
    $sql = "DROP TABLE IF EXISTS $table3";
    $wpdb->query($sql);
    
    $table4= $wpdb->prefix . 'periodicites';
    $sql = "DROP TABLE IF EXISTS $table4";
    $wpdb->query($sql);
    
    $table5= $wpdb->prefix . 'event_periodicite';
    $sql = "DROP TABLE IF EXISTS $table5";
    $wpdb->query($sql);
    
    //-----------------------------------------------------------destruction des pages
    $post = get_page_by_title('Événements');
    wp_delete_post($post->ID, true);
    
    $post = get_page_by_title('Inscription');
    wp_delete_post($post->ID, true);
    
    $post = get_page_by_title('Liste des Inscriptions');
    wp_delete_post($post->ID, true);
    
    $post = get_page_by_title('Création d\'un Événement');
    wp_delete_post($post->ID, true);
    
    //-----------------------------------------------------------destruction des options
    delete_option('DevostC_options');
}
?>