<?php
class Controller_accueil extends Controller
{
    public function action_accueil()
    {
		/*
		Permet d'accéder a la page d'accueil.
		Cette fonction est de complexité O(1) c'est a dire constante.
		 */

        $data = [
			"title"=>"Page d'Accueil",
        ];
        $this->render("accueil", $data);
    }
	
	
    public function action_default()
    {
        $this->action_accueil();
    }
    public function action_ajout()
    {
		/*
		Cette fonction verifie si l'utilisateur est bon grace a beaucoup de test,si il y a un post, si le fichier envoyé est 'Valide' a l'upload,
			et si tout est valide il envoie le fichier dans la base de données et le met dans son dossier.
			Si il y a une erreur un message d'erreur est envoyé a la page d'envoie de fichier et il n'est pas implémenter.
			Si rien est valide il renvoie a la page de login (si ya pas de session).Si ya une session il renvoie a la page étudiant.
		La fonction action_ajout() effectue des comparaisons et des appels de fonctions donc sa complexité est généralement O(1) pour les comparaisons et
		 O(n) pour les appels de fonctions. 
		 En résumé la complexité globale de cette fonction est O(1) + O(1) + O(n) + O(taille_du_fichier) qui est principalement dominé par la taille du fichier à déplacer.
		*/
        if(isset($_POST['submit'])){
				if (isset($_SESSION['attribut'])){
					$session=$_SESSION["attribut"];
					if(sessionValide($session)){
					
						$m = Model::getModel();
						$user=$session["n"];
						$userE=$m->userExist($user);
							
						if ($userE!=false){
							if (userValide($session,$userE)){
								$fichier = $_FILES['file']['name'];
								$taille_maximal = 20000000;
								$taille = filesize($_FILES['file']['tmp_name']);
								$extensions = array('.png','.jpg','.jpeg','.gif','.pdf','.PNG','.JPG','.JPEG','.GIF','.PDF');
								$extension = strrchr($fichier,'.');
								
								if(!in_array($extension,$extensions)){
									$error = "Vous devez déposer un autre fichier";
								}
								if($taille > $taille_maximal){
									$error = "Le fichier est trop volumineux veuillez réessayer";
								}
								
								if(!isset($error)){
									$doc=typeDeDocument($_POST["typeDeDocument"]);
									$fichier = preg_replace('/([^.a-z0-9]+)/i',' -',$fichier);
									
										
									$file=$_FILES['file']['name'];
									$file=e($file);
									if ($file!=$fichier||$fichier!=$_FILES['file']['name']){
										$error='Veuillez enlever les caractères spéciaux du nom de votre document afin de déposer votre fichier !';
									}
									$exist=$m->nomDocExist($user,$doc,$file);
									
									if (!$exist && !isset($error)){
										move_uploaded_file($_FILES['file']['tmp_name'],"Document_Stage/".$user.'/'.$doc.'/'.$fichier);
										
										//Récupération de l'objet PDO
										
										$m = Model::getModel();
										$upload = $m->upload_fichier($file,$doc,$user);
										$session['last50']=$m->last50($user);
										$_SESSION['attribut']=$session;
									}
									elseif ($exist){
										$error='Vous avez déjà un déposé un document en ce nom. <br/>Veuillez changer le nom du document !';
									}
									elseif ($error==''){
										$error='Veuillez changer le nom du document afin de le déposer !';
										
									}
									
								}
								//Affichage de la vue
								$data = [
									"title" => "Page d'Accueil Etudiant",
									"attribut"=>$session,
									"type"=>$_POST['typeDeDocument']
								];
								if (isset($upload)) {
									if ($upload){
										$data["message"] = "Votre fichier a été ajouté.";
									} else {
										$data["message"] = "Il y a eu une erreur! Votre fichier n'a pas pu être transferé !";
									}
								}
								elseif(isset($error)){
									$data["message"]=$error;
								}
								$this->render("file_upload_exemple", $data);
							}
						}	
						
					}
				}
		}
		
		elseif (isset($_SESSION['attribut'])){
			$session=$_SESSION["attribut"];
			if(sessionValide($session)){
				$m = Model::getModel();
				$user=$session["n"];
				$userE=$m->userExist($user);
					
				if ($userE!=false){
					if (userValide($session,$userE)){
						$_SESSION["attribut"]=$session;
						$role=$session["role"];
							
						if ($role=="Étudiant"){
							
							$this->render("etudiant_profile",$session);//renvoie le tableau directement pris de la session
						}
					}
					
				}
			}
				
		}
	$data = [
		"title" => "Page d'authentification",
        ];
	$this->render("login", $data);
	
	}
	
	
	public function action_page_ajout(){
		/*
		Cette fonction amène a une page spécial proposant de créer des Département/Composante,des Groupes, des Utilisateur, ou de faire une auto incrémentation des tables.
		La complexité de cette fonction est de O(n) où n est le nombre de conditions vérifiées dans la fonction. 
		*/
		if (isset($_SESSION['attribut'])){
			$session=$_SESSION["attribut"];
			if(sessionValide($session)){
				$m = Model::getModel();
				$user=$session["n"];
				$userE=$m->userExist($user);
				
				if ($userE!=false){
					if (userValide($session,$userE)){
					$_SESSION["attribut"]=$session;
					if(isset($_GET["type"])){
						if(typeValide($_GET["type"])){
							$data = [
							"title"=>"Page de ",
							"attribut" => $session,
							"type"=>$_GET["type"]
							];
							$session['type']=$data['type'];
							$_SESSION["attribut"]=$session;
							$data["title"]=$data["title"].$userE['personne'];
							$this->render("file_upload_exemple", $data);
							
							}
						}
					$this->render("etudiant_profile",$session);//renvoie le tableau directement pris de la session
					}
				}
			}
		}
	$data = [
		"title"=>"Page d'authenfication",
		];
	$this->render("login", $data);
	}
	
    public function action_default()
    {
        $this->action_page_ajout();
    }
	
    public function action_commentaire(){
		/*
		Cette fonction verifie si il y a un post, si l'utilisateur est bon grace a beaucoup de test,
			si le post est valide (si il n'est pas vide), et qu'il a les info nécéssaire,
			et si tout est valide il ajoute un commentaire dans la base de données.
			Si rien est valide il renvoie a la page de login (si ya pas de session).
		La complexité de cette fonction est de O(1) car elle ne fait qu'une seule requête à la base de données pour ajouter un commentaire et une seule boucle pour afficher les commentaires.
		*/

		if(isset($_POST['docID'])&&isset($_POST['user'])&&isset($_POST['commentaire'])){
            $docID = $_POST['docID'];
			if (isset($_SESSION["attribut"])){//si le cookie attribut existe et a les bon attribut
				$session=$_SESSION["attribut"];
				if(sessionValide($session)){
					$m = Model::getModel();
					$user=$session["n"];
					$userE=$m->userExist($user);
						
					if ($userE!=false && $_POST['commentaire']!=''){
						$m->ajoutCommentaire($_POST['docID'],$_POST['user'],$_POST['commentaire']);
						$data=[
							"document" => [
								'docID'=>$docID,
								'nomPersonne'=>$_POST['nomPersonne'],
								'nomDoc'=>$_POST['nomDoc'],
								'url'=>$_POST['url']],
							"commentaires"=>$m->takeCommentaires($docID)
						];
						$_SESSION['commentaires']=$data['commentaires'];
						$this->render('commentaire',$data);
					}
				}
			}

		} 
	
	$data = [
			"title"=>"Page d'authentification"
        ];
        $this->render("login", $data);
	}
	
	
	public function action_page_commentaire(){
		/*
		Cette fonction verifie si il y a un post, si l'utilisateur est bon grace a beaucoup de test,
			si le post est valide (si il n'est pas vide), et qu'il a les info nécéssaire.
			et si tout est valide il nous amène a la page de commentaire avec les données nécéssaire a l'affichage de la page.
			Si rien est valide il renvoie a la page de login (si ya pas de session).
		La complexité de cette fonction est similaire à celle de la fonction action_commentaire, c'est-à-dire O(1) pour vérifier la validité de la session et de l'utilisateur, 
		O(1) pour récupérer les commentaires et les informations du document, et O(1) pour afficher la vue. 
		*/
        if(isset($_POST['docID'])&&isset($_POST['nomPersonne'])&&isset($_POST['nomDoc'])&&isset($_POST['url'])){
            $docID = $_POST['docID'];
			if (isset($_SESSION["attribut"])){
				$session=$_SESSION["attribut"];
				if(sessionValide($session)){
					$m = Model::getModel();
					$user=$session["n"];
					$userE=$m->userExist($user);
						
					if ($userE!=false){
						$data=[
							"document" => [
								'docID'=>$docID,
								'nomPersonne'=>$_POST['nomPersonne'],
								'nomDoc'=>$_POST['nomDoc'],
								'url'=>$_POST['url']],
							"commentaires"=>$m->takeCommentaires($docID)
						];
						$_SESSION['commentaires']=$data['commentaires'];
						$_SESSION['document']=$data['document'];
						$this->render('commentaire',$data);
						
					}
				}
			}
		}

		$data = [
			"title"=>"Page d'authentification"
        ];
        $this->render("login", $data);
	}
	
	public function action_default()
    {
        $this->action_page_commentaire();
    }
	
    public function action_connexion()
    {
		/*
		Cette fonction verifie si il y a un post, si il y en a pas elle verifie si il y a une session valide de présente, 
			si il y en a pas elle envoie sur la page de login (connexion),
			vérifie si  l'utilisateur existe, si il n'existe pas elle renvoie sur la page de login,
			si le mot de passe est bon, si pas bon renvoie sur le login avec un message d'erreur,
			si il y a pas de session d'active, il la créer, si une existe il connecte juste avec la nouvelle session en modifiant l'ancienne.
		La complexité de cette fonction est O(n), car elle effectue plusieurs vérifications de données (si le post existe,
		si l'utilisateur existe, si le mot de passe est valide, etc.) et des appels à des fonctions de modèle (userExist, getM...) 
		qui peuvent avoir des complexités variables. Cependant, dans l'ensemble, la complexité globale reste linéaire 
		
		*/
		if (isset($_POST["user_login"])){//si on le post
			$user=$_POST["user_login"];
			$m = Model::getModel();
			$userE=$m->userExist($user);//renvoie tableau avec les donnée nécésssaire si existe
			if ($userE!=false){//utilisateur exist
				if (password_verify($_POST["user_password"],$m->getM($user))/*true ou false*/){
					if (!isset($_SESSION['attribut'])){

						if ($userE['role']=="Étudiant"){
							$userE['last50']=$m->last50($user);
							$_SESSION['attribut']=$userE;
							$this->render("etudiant_profile",$userE);
						}
						
						else{
							$userE['documents']=$m->derniersDoc();
							$_SESSION['attribut']=$userE;
							$_SESSION['documents']=$userE['documents'];
							
							$this->render("professeur_profile",$userE);//

						}
						
					}
					else{
							$_SESSION['attribut']=$userE;
							$role=$userE["role"];
							
							if ($role=="Étudiant"){
								$userE['last50']=$m->last50($user);
								$this->render("etudiant_profile",$userE);//renvoie le tableau directement pris du session
							}
							elseif($role=="Enseignant Tuteur"){
								$userE['documents']=$m->derniersDoc();
								$_SESSION['documents']=$userE['documents'];
								$this->render("professeur_profile",$userE);//
								
							}
							
							elseif ($role=="Enseignant Validateur"){
								$userE['documents']=$m->derniersDoc();
								$_SESSION['documents']=$userE['documents'];
								$this->render("professeur_profile",$userE);
							}
							
							elseif ($role=="Membre du Secrétariat"){
								$userE['documents']=$m->derniersDoc();
								$_SESSION['documents']=$userE['documents'];
								$this->render("professeur_profile",$userE);
							}
							
							elseif ($role=="Coordinatrice de stage"){
								$userE['documents']=$m->derniersDoc();
								$_SESSION['documents']=$userE['documents'];
								$this->render("professeur_profile",$userE);
							}
						

					}
					
				}
			}
			
			$data = [
				"title" => "Page d'accueil de ",//a changer
				"message"=>"Le nom d'utilisateur ou le mot de passe sont incorrecte"
			];
			$this->render("login", $data);//renvoie le message
			
		}//fin de si on post 
		
		
		
		else{//sinon ... generalement si ya une session existante
			if (isset($_SESSION["attribut"])){//si le session attribut existe 
				$session=$_SESSION["attribut"];
				if(sessionValide($session)){//si le session attribut existe et a les bon attribut
					$m = Model::getModel();
					$user=$session["n"];
					$userE=$m->userExist($user);
					
					if ($userE!=false){
						if (userValide($session,$userE)){
							
							$_SESSION["attribut"]=$session;
							$role=$session["role"];
							
							if ($role=="Étudiant"){
								$session['last50']=$m->last50($user);
								$_SESSION["attribut"]=$session;
								$this->render("etudiant_profile",$session);//renvoie le tableau directement pris du session
							}
							elseif($role=="Enseignant Tuteur"){
								$userE['documents']=$m->derniersDoc();
								$_SESSION['documents']=$userE['documents'];
								$this->render("professeur_profile",$session);//
								
							}
							
							elseif ($role=="Enseignant Validateur"){
								$userE['documents']=$m->derniersDoc();
								$_SESSION['documents']=$userE['documents'];
								$this->render("professeur_profile",$session);
							}
							
							elseif ($role=="Membre du Secrétariat"){
								$userE['documents']=$m->derniersDoc();
								$_SESSION['documents']=$userE['documents'];
								$this->render("professeur_profile",$session);
							}
							
							elseif ($role=="Coordinatrice de stage"){
								$userE['documents']=$m->derniersDoc();
								$_SESSION['documents']=$userE['documents'];
								$this->render("professeur_profile",$session);
							}
						}
					}
				}
				
				
			}
			$data = [
				"title" => "Page d'accueil de ",//a changer
				"message"=>""
			];
			$this->render("login",$data);
			
		}
	
	}//fin de l'action de connexion
		
	
	public function action_page_connexion()
    {
		/*
		Cette fonction vérifie si il y a une session de présente existe, et si elle est valide, et si non, alors elle amene le login.
		La complexité de cette fonction est O(1) car elle vérifie simplement l'existence et la validité d'une session. Si la session est valide,
		 elle effectue quelques vérifications supplémentaires pour déterminer le rôle de l'utilisateur et rendre la vue appropriée. 
		*/
		if (isset($_SESSION["attribut"])){//si le session attribut existe et a les bon attribut
			$session=$_SESSION["attribut"];
			if(sessionValide($session)){
				$m = Model::getModel();
				$user=$session["n"];
				$userE=$m->userExist($user);
					
				if ($userE!=false){
					if (userValide($session,$userE)){
						$_SESSION["attribut"]=$session;
						$role=$session["role"];
							
						if ($role=="Étudiant"){
							$session['last50']=$m->last50($user);
							$_SESSION["attribut"]=$session;
							$this->render("etudiant_profile",$session);//renvoie le tableau directement pris du session
						}
						elseif($role=="Enseignant Tuteur"){
							$this->render("professeur_profile",$session);//
								
						}
							
						elseif ($role=="Enseignant Validateur"){
							$this->render("professeur_profile",$session);
						}
							
						elseif ($role=="Membre du Secrétariat"){
							$this->render("professeur_profile",$session);
						}
							
						elseif ($role=="Coordinatrice de stage"){
							$this->render("professeur_profile",$session);
						}
					}
				}
			}
			
		}
		$data = [
			"title" => "Page d'autentification",
		];
		$this->render("login", $data);
    }

    public function action_default()
    {
        $this->action_page_connexion();
    }

    public function action_deconnexion()
    {
		/*
		Cette fonction vide toute les session pouvant existées et nous envoie a la page de login (connexion).
		La complexité de cette fonction est O(1), car elle effectue un nombre constant d'opérations, 
		qui sont la suppression des variables de session et la redirection vers la page de connexion.
		*/
		$_SESSION['attribut']="";
		$_SESSION['document']="";
		$_SESSION['documents']="";
		$_SESSION['commentaires']="";
		
        $data = [
            "title" => "Deconnexion",
        ];
        $this->render("login", $data);
    }

    public function action_default()
    {
        $this->action_deconnexion();
    }

    public function action_accueil_enseignant()
    {
		/*
		Cette fonction verifie si il y a une fonction qui existe, et si elle est complètement valide,
			et si cela est bon elle renvoie les informations nécessaire au bonne affichage de la page.
		La complexité de cette fonction est de O(1) car elle vérifie simplement les conditions d'existence
		 et de validité des sessions et des utilisateurs, puis rend la vue en fonction du rôle de l'utilisateur
		*/
		if (isset($_SESSION["attribut"])){
			$session=$_SESSION["attribut"];
			if(sessionValide($session)){
				$m = Model::getModel();
				$user=$session["n"];
				$userE=$m->userExist($user);
					
				if ($userE!=false){
					if (userValide($session,$userE)){
						$_SESSION["attribut"]=$session;
						$role=$session["role"];
							
						if ($role!="Étudiant"){
							$_SESSION["attribut"]=$session;
							$session['documents']=$m->derniersDoc();
							$_SESSION["documents"]=$session['documents'];
							$this->render("professeur_profile",$session);//renvoie le tableau directement pris de la session
						}
					}
					
				}
			}
			
		}
        $data = [
			"title"=>"Page d'authentification"
        ];
        $this->render("login", $data);
    }
	
	
public function action_default()
    {
        $this->action_accueil_enseignant();
    }

    public function action_accueil_etudiant()
    {
		/*
		Cette fonction verifie si il y a une fonction qui existe, et si elle est complètement valide,
			et si cela est bon elle renvoie les informations nécessaire au bonne affichage de la page.
		Cette fonctino a une complexité de O(n) car elle effectue plusieurs vérifications successives sur des données de tailles différentes.
		*/
		if (isset($_SESSION["attribut"])){
			$session=$_SESSION["attribut"];
			if(sessionValide($session)){
				$m = Model::getModel();
				$user=$session["n"];
				$userE=$m->userExist($user);
					
				if ($userE!=false){
					if (userValide($session,$userE)){
						$_SESSION["attribut"]=$session;
						$role=$session["role"];
							
						if ($role=="Étudiant"){
							$session['last50']=$m->last50($user);
							$_SESSION["attribut"]=$session;
							$this->render("etudiant_profile",$session);//renvoie le tableau directement pris de la session
						}
					}
					
				}
			}
			
		}
        $data = [
			"title"=>"Page d'authentification"
        ];
        $this->render("login", $data);
    }
	
	
public function action_default()
    {
        $this->action_accueil_etudiant();
    }

    public function action_default()
    {
		
        $this->action_page_ajout();
    }

	public function action_page_ajout()
    {
		/*
		Cette fonction amène a une page spécial proposant de créer des Département/Composante,
			des Groupes, des Utilisateur, ou de faire une auto incrémentation des tables.
		La complexité de cette fonction est constante O(1) car elle ne contient aucune boucle, 
		elle n'appel que la fonction render() dont la complexité ne dépend pas de la taille d'éléments en entrée.
		*/
		
        $data = [
            "title" => "Inscription",
        ];
        $this->render("ajout", $data);
    }
	
	public function action_page_comp()
    {
		/*
		Cette fonction réccupère tous les Départements existant et les envoie sur 
			la page d'ajout de Département/Composante.
		La complexité de cette fonction est également constante O(1) car elle effectue un nombre constant d'opérations indépendantes de la taille des entrées. 	
		*/
        $m = Model::getModel();
		$departements=$m->takeDepartements();
        $data = [
            "title" => "Inscription",
			'departements'=>$departements
        ];
        $this->render("create_composante", $data);
    }
	
	public function action_page_groupe1()
    {
		/*
		Cette fonction réccupère tous les Départements existant et les envoie sur 
			la première page d'ajout de Groupes.
		La complexité de cette fonction est également constante O(1) car elle effectue un nombre constant d'opérations indépendantes de la taille des entrées.
			
		*/
        $m = Model::getModel();
		$departements=$m->takeDepartements();
        $data = [
            "title" => "Inscription",
			'departements'=>$departements
        ];
        $this->render("create_groupe1", $data);
    }
	
	public function action_page_groupe2()
    {
		/*
		Cette fonction réccupère le département sélectionner en paramètre sur la première page
			et reccupère avec les Composantes qui lui sont associer,
			et les envoies sur la deuxième page d'ajout de Groupes.
		La complexité de cette fonction est également constante O(1) car elle effectue un nombre constant d'opérations.
		Si les données sont postées, elle effectue un nombre constant d'opérations indépendantes de la taille des entrées. Elle appelle une fonction (getModel) qui ne dépend pas de la taille des entrées.
		*/
		if(isset($_POST['submit'])){
			$m = Model::getModel();
			$composantes=$m->takeComposantes($_POST['departement']);
			$data = [
				"title" => "Inscription",
				'departement'=>$_POST['departement'],
				'composantes'=>$composantes
			];
			$this->render("create_groupe2", $data);
		}
        $data = [
            "title" => "PA"
        ];
        $this->render("accueil", $data);
    }
	
	
	public function action_ajout_composante()
    {
		/*
		Cette fonction réccupère les informations sélectionner en paramètre sur la page des Département/Composante
			et ajoute les information dans la base de données, biensur il faut qu'elles soient valide.
		La complexité de cette fonction est constante O(1).L'appel de la fonction render() ne modifie rien car elle est elle meme est de complexité constante.
		*/
		if(isset($_POST['submit'])){
			
			if (isset($_POST['departement'])&&isset($_POST['composante'])){
				$departement=$_POST['departement'];
				if ($departement=='autre' && $_POST['autre']!=''){
					$departement=$_POST['autre'];
				}
				if($departement!=''){
					$m = Model::getModel();
					
					$m->ajoutComp($departement,$_POST['composante']);
					$this->action_page_comp();
					
				}
			}
		}
		$data = [
			"title" => "PA"
		];
		$this->render("accueil", $data);
    }
	
	
	public function action_ajout_groupe()
    {
		/*
		Cette fonction réccupère les informations sélectionner en paramètre sur la page des Groupes
			et ajoute les information dans la base de données, biensur il faut qu'elles soient valide.
		La complexité de cette fonction est également constante O(1).
		*/
		if(isset($_POST['submit'])){
			
			if (isset($_POST['departement'])&&isset($_POST['composante'])&&isset($_POST["groupe"])){
				if ($_POST["groupe"]!=''){
					$infos=[
						"groupe"=>$_POST["groupe"],
						"niveau"=>$_POST["niveau"],
						"promo"=>$_POST["promo"],
						
					];
					$m = Model::getModel();
					$m->ajoutGroupe($_POST['departement'],$_POST['composante'],$infos);
					$this->action_page_groupe1();
				
				}
			}
		}
		$data = [
			"title" => "PA"
		];
		$this->render("accueil", $data);
    }
	

    public function action_inscription_page1()
    {
		/*
		Cette fonction réccupère tous les Départements existant et les envoie sur 
			la première page d'ajout d'Utilisateur.
		La complexité de cette fonction est constante (O(1)) car elle effectue un nombre constant d'opérations indépendantes de la taille des entrées. 
		Elle appelle une fonction (getModel) qui ne dépend pas de la taille des entrées.
		*/
		$m = Model::getModel();
		$departements=$m->takeDepartements();
        $data = [
            "title" => "Inscription Etudiant",
			'departements'=>$departements
        ];
        $this->render("inscription_page1", $data);
    }
	
	
	
	public function action_inscription_page2()
    {
		/*
		Cette fonction réccupère le département sélectionner en paramètre sur la première page
			et reccupère avec les Composantes qui lui sont associer, 
			et les envoies sur la deuxième page d'ajout d'Utilisateur.
		
		*/
		if(isset($_POST['submit'])){
			if (isset($_POST['nom'])&&isset($_POST['prenom'])&&isset($_POST['mail'])&&isset($_POST['role'])&&isset($_POST['departement'])){
				if (preg_match('/^.+@[a-z]+\.[a-z]+$/',$_POST['mail'])){
					$m = Model::getModel();
					$composantes=$m->takeComposantes($_POST['departement']);
					$data=[
						"nom"=>e($_POST['nom']),
						"prenom"=>e($_POST['prenom']),
						"mail"=>$_POST['mail'],
						"role"=>$_POST['role'],
						'departement'=>$_POST['departement'],
						'composantes'=>$composantes
					];
					$this->render("inscription_page2", $data);
				}
			}
		}
        $data = [
            "title" => "PA",
        ];
        $this->render("accueil", $data);
    }

	public function action_inscription_page2_1()
    {
		/*
		Cette fonction réccupère le département et la composante sélectionner en paramètre sur la deuxième page
			et reccupère avec les Groupes qui lui sont associer, et d'autres informations,
			et les envoies sur cette page d'ajout d'Utilisateur.
		Cette page n'est visible que si on créer un étudiant.
		Cette fonction possède également une complexité constante car aucune boucle n'est effectuée.
		*/
		if(isset($_POST['submit'])){
			if (isset($_POST['nom'])&&isset($_POST['prenom'])&&isset($_POST['mail'])&&isset($_POST['role'])&&isset($_POST['departement'])&&isset($_POST['composante'])){
				
				$m = Model::getModel();
				$groupes=$m->takeGroupes($_POST['departement'],$_POST['composante']);
				$data=[
					"nom"=>e($_POST['nom']),
					"prenom"=>e($_POST['prenom']),
					"mail"=>$_POST['mail'],
					"role"=>$_POST['role'],
					'departement'=>$_POST['departement'],
					'composante'=>$_POST['composante'],
					'groupes'=>$groupes
				];
				$this->render("inscription_page2_1", $data);
			
			}
		}
        $data = [
            "title" => "PA",
        ];
        $this->render("accueil", $data);}



	public function action_inscription_page3()
    {
		/*
		Cette fonction réccupère le département, la composante et le groupe, si étudiant, sélectionner en paramètre sur la dernière page
			et reccupère avec les Promos qui lui sont associer, et d'autres informations,
			et les envoies sur cette page d'ajout d'Utilisateur.
		La complexité de cette fonction est également constante O(1) dans la mesure où le nombre d'éléments a vérifier n'influent pas spécifiquement le temps d'execution ou l'espace mémoire.
		
		*/
		if (isset($_POST['submit'])){
			if (isset($_POST['nom'])&&isset($_POST['prenom'])&&isset($_POST['mail'])&&isset($_POST['role'])&&isset($_POST['departement'])&&isset($_POST['composante'])){
			
				$data['nom']=$_POST['nom'];
				$data['prenom']=$_POST['prenom'];
				$data['mail']=$_POST['mail'];
				$data['role']=$_POST['role'];
				$m = Model::getModel();
				$ex=$m->personneExist($data);
				if (!$ex){
					$data['departement']=$_POST['departement'];
					$data['composante']=$_POST['composante'];
					if ($data['role']=='e'){
						$data["groupe"]=$_POST["groupe"];
						$data['promos']=$m->takePromos($_POST['departement'],$_POST['composante'],$_POST["groupe"]);
					}
					$d=[
						'data'=>$data
                    ];
					
					$this->render("inscription_page3", $d);
				}
				
			}
		}
		
        $dat = [
            "title" => "PA",
        ];
        $this->render("accueil", $dat);
    }
	
	
	
	public function action_inscription()
    {
		/*
		Cette fonction réccupère toutes les information sélectionner en paramètre sur la dernière page
			et beaucoups d'autres informations des pages précédentes,
			et créer un Utilisateur avec, il créer aussi ses dossiers, si étudiant.
		Elle renvoie sur la page Ajout. Donc a la première action.
		La complexité de cette fonction est O(n) c'est à dire linéaire car au vu des vérifications et des dossiers à créer, n est sucesptible de grandir en focntion du nombre de donnée qui s'ajoute.
		
		*/
		if (isset($_POST['submit'])){
			if (isset($_POST['nom'])&&isset($_POST['prenom'])&&isset($_POST['mail'])&&isset($_POST['role'])&&isset($_POST['departement'])&&isset($_POST['composante'])&&isset($_POST['user'])&&isset($_POST['mdp'])){
			
				$m = Model::getModel();
				$userE=$m->userExist($_POST['user']);//existe ou pas
				
				if ($userE==false){//si il existe pas
						$data=[
							"nom"=>$_POST['nom'],
							"prenom"=>$_POST['prenom'],
							"mail"=>$_POST['mail'],
							"role"=>$_POST['role'],
							"departement"=>$_POST['departement'],
							"composante"=>$_POST['composante'],
							"user"=>$_POST['user'],
							"mdp"=>$_POST['mdp']
							];
							if ($data['role']=='e'){	/*créer une vérif si le groupe est valide*/
								$data["groupe"]=$_POST["groupe"];
								$data["promo"]=$_POST["promo"];
								$chemin= "Document_Stage/".$data['user']."/";  //ETUDIANT ID A REMPLACER PAR $_SESSION[Student_ID]
								dirname($chemin);
								$f=mkdir($chemin,0700,true);
								dirname($chemin.'Bordereau_d-offre_de_stage/');
								$f=mkdir($chemin.'Bordereau_d-offre_de_stage/',0700,true);
								dirname($chemin.'CV/');
								$f=mkdir($chemin.'CV/',0700,true);
								dirname($chemin.'Lettre_de_Motivation/');
								$f=mkdir($chemin.'Lettre_de_Motivation/',0700,true);
								dirname($chemin.'Journal_de_Bord/');
								$f=mkdir($chemin.'Journal_de_Bord/',0700,true);
								dirname($chemin.'Mini_Rapport_de_Stage/');
								$f=mkdir($chemin.'Mini_Rapport_de_Stage/',0700,true);
								dirname($chemin.'Rapport_final/');
								$f=mkdir($chemin.'Rapport_final/',0700,true);
							}
							
							$m->userCreater($data);
							
							$this->action_inscription_page1();
						}
					}
					
				}
				
	
			$d=[
				'data'=>'data'
            ];
			$this->render("accueil", $d);
	}
	
	public function action_auto_inscription(){
		/*
		Cette fonction contient un tableau de Département/Composante, un autre de Groupes associer au tableau d'avant,
			et enfin un tableau contenant  32 étudiants et 4 enseignant, et les ajoutes tous dans la base de données,
			cette fonction est une fonction nous permettant de faire des tests.
        Cette fonction dispose d'une complexité de O(1) car elle ne prend aucun élément en paramètre.
        De plus, elle contient certe des boucles mais lors de l'execution elles restent constantes.
		*/
		$departComp=[
			["departement"=>'Informatique',"composante"=>'B.U.T. Informatique'],
			["departement"=>'Informatique',"composante"=>'B.U.T. Passerelle'],
			["departement"=>'Informatique',"composante"=>'B.U.T. STID'],
			["departement"=>'Informatique',"composante"=>'B.U.T. GEII'],
			["departement"=>'Informatique',"composante"=>'Licence Professionnelle MICDTL'],
			["departement"=>'Informatique',"composante"=>'B.U.T. Réseaux & Télécommunications'],
			["departement"=>'Électronique',"composante"=>'Licence Professionnelle MECSE'],
			["departement"=>'Électronique',"composante"=>'Licence Professionnelle EON'],
			["departement"=>'Réseaux/Télécoms',"composante"=>'B.U.T. Réseaux & Télécommunications'],
			["departement"=>'Réseaux/Télécoms',"composante"=>'B.U.T. GEII'],
			["departement"=>'Réseaux/Télécoms',"composante"=>'B.U.T. Informatique'],
			["departement"=>'Ressources Humaines',"composante"=>'B.U.T. GEA'],
			["departement"=>'Droit/Juridique',"composante"=>'B.U.T. Carrières Juridiques'],
			["departement"=>'Droit/Juridique',"composante"=>'Licence Professionnelle Métiers du Notariat'],
			["departement"=>'Assurance/Banque',"composante"=>'B.U.T. Carrières Juridiques'],
			["departement"=>'Assurance/Banque',"composante"=>'B.U.T. GEA'],
			["departement"=>'Assurance/Banque',"composante"=>'Licence Professionnelle ABF'],
			["departement"=>'Gestion des Entreprises',"composante"=>'Licence Professionnelle CP']
		];
		
		$groupes=[
			["departement"=>'Informatique',"composante"=>'B.U.T. Informatique',"groupe"=>'Athos',"niveau"=>2,"promo"=>'2022-2023'],
			["departement"=>'Informatique',"composante"=>'B.U.T. Informatique',"groupe"=>'Aramis',"niveau"=>2,"promo"=>'2022-2023'],
			["departement"=>'Informatique',"composante"=>'B.U.T. Informatique',"groupe"=>'Porthos',"niveau"=>2,"promo"=>'2022-2023'],
			["departement"=>'Informatique',"composante"=>'B.U.T. Informatique',"groupe"=>'Lovelace',"niveau"=>3,"promo"=>'2023-2024'],
			["departement"=>'Informatique',"composante"=>'B.U.T. Informatique',"groupe"=>'Hopper',"niveau"=>3,"promo"=>'2023-2024'],
			["departement"=>'Informatique',"composante"=>'B.U.T. Informatique',"groupe"=>'Lepaute',"niveau"=>3,"promo"=>'2023-2024'],
			
			["departement"=>'Informatique',"composante"=>'B.U.T. Passerelle',"groupe"=>'Passerelle',"niveau"=>2,"promo"=>'2022-2023'],
			["departement"=>'Informatique',"composante"=>'B.U.T. Passerelle',"groupe"=>'Passerelle',"niveau"=>2,"promo"=>'2023-2024'],
			
			["departement"=>'Informatique',"composante"=>'B.U.T. STID',"groupe"=>'Staatskunde',"niveau"=>2,"promo"=>'2022-2023'],
			
			["departement"=>'Informatique',"composante"=>'B.U.T. GEII',"groupe"=>'Lavoisier',"niveau"=>2,"promo"=>'2022-2023'],
			
			["departement"=>'Informatique',"composante"=>'Licence Professionnelle MICDTL',"groupe"=>'Hammourabi',"niveau"=>2,"promo"=>'2022-2023'],
			
			["departement"=>'Informatique',"composante"=>'B.U.T. Réseaux & Télécommunications',"groupe"=>'Hooke',"niveau"=>2,"promo"=>'2022-2023'],
			
			["departement"=>'Électronique',"composante"=>'Licence Professionnelle MECSE',"groupe"=>'Pentode',"niveau"=>2,"promo"=>'2022-2023'],
			
			["departement"=>'Électronique',"composante"=>'Licence Professionnelle EON',"groupe"=>'Feynman',"niveau"=>2,"promo"=>'2022-2023'],
			
			["departement"=>'Réseaux/Télécoms',"composante"=>'B.U.T. Réseaux & Télécommunications',"groupe"=>'Hooke',"niveau"=>2,"promo"=>'2022-2023'],
			
			["departement"=>'Réseaux/Télécoms',"composante"=>'B.U.T. GEII',"groupe"=>'Lavoisier',"niveau"=>2,"promo"=>'2022-2023'],
			
			["departement"=>'Réseaux/Télécoms',"composante"=>'B.U.T. Informatique',"groupe"=>'Network',"niveau"=>2,"promo"=>'2022-2023'],
			
			["departement"=>'Ressources Humaines',"composante"=>'B.U.T. GEA',"groupe"=>'Cantet',"niveau"=>2,"promo"=>'2022-2023'],
			
			["departement"=>'Droit/Juridique',"composante"=>'B.U.T. Carrières Juridiques',"groupe"=>'Feuchère',"niveau"=>2,"promo"=>'2022-2023'],
			
			["departement"=>'Droit/Juridique',"composante"=>'Licence Professionnelle Métiers du Notariat',"groupe"=>'Ambrosiano',"niveau"=>2,"promo"=>'2022-2023'],
			
			["departement"=>'Assurance/Banque',"composante"=>'B.U.T. Carrières Juridiques',"groupe"=>'Feuchère',"niveau"=>2,"promo"=>'2022-2023'],
			
			["departement"=>'Assurance/Banque',"composante"=>'B.U.T. GEA',"groupe"=>'Cantet',"niveau"=>2,"promo"=>'2022-2023'],
			
			["departement"=>'Assurance/Banque',"composante"=>'Licence Professionnelle ABF',"groupe"=>'Trapeza',"niveau"=>2,"promo"=>'2022-2023'],
			
			["departement"=>'Gestion des Entreprises',"composante"=>'Licence Professionnelle CP',"groupe"=>'Sumer',"niveau"=>2,"promo"=>'2022-2023']
		];
		
		$m = Model::getModel();
		foreach($departComp as $infos){
			$m->ajoutComp($infos['departement'],$infos['composante']);
			
		}
		foreach($groupes as $groupe){
				$m->ajoutGroupe($groupe['departement'],$groupe['composante'],$groupe);
				
			}
			
			
		$users=[
			["nom"=>'Haude',"prenom"=>'Aucéane',"mail"=>'haude.auceane15@gmail.com',"role"=>'e',"departement"=>'Informatique',"composante"=>'B.U.T. Informatique',"groupe"=>'Athos',"promo"=>'2022-2023',"user"=>'12107562',"mdp"=>'azerty'],
			["nom"=>'Portier',"prenom"=>'Moïse',"mail"=>'portier.moise@gmail.com',"role"=>'e',"departement"=>'Informatique',"composante"=>'B.U.T. Informatique',"groupe"=>'Athos',"promo"=>'2022-2023',"user"=>'12107900',"mdp"=>'azerty'],
			["nom"=>'Morgan',"prenom"=>'Xavier',"mail"=>'morgan.xavier@gmail.com',"role"=>'e',"departement"=>'Informatique',"composante"=>'B.U.T. Informatique',"groupe"=>'Athos',"promo"=>'2022-2023',"user"=>'12102301',"mdp"=>'azerty'],
			["nom"=>'Baten',"prenom"=>'Liam',"mail"=>'baten.liam@gmail.com',"role"=>'e',"departement"=>'Informatique',"composante"=>'B.U.T. Informatique',"groupe"=>'Athos',"promo"=>'2022-2023',"user"=>'12105095',"mdp"=>'azerty'],
			["nom"=>'Brunelle',"prenom"=>'Janine',"mail"=>'brunelle.janine@gmail.com',"role"=>'e',"departement"=>'Informatique',"composante"=>'B.U.T. Informatique',"groupe"=>'Athos',"promo"=>'2022-2023',"user"=>'12107288',"mdp"=>'azerty'],
			["nom"=>'Marchal',"prenom"=>'Paola',"mail"=>'marchal.paola@gmail.com',"role"=>'e',"departement"=>'Informatique',"composante"=>'B.U.T. Informatique',"groupe"=>'Aramis',"promo"=>'2022-2023',"user"=>'12105055',"mdp"=>'azerty'],
			["nom"=>'Vallée',"prenom"=>'Tatiana',"mail"=>'vallee.tatiana@gmail.com',"role"=>'e',"departement"=>'Informatique',"composante"=>'B.U.T. Informatique',"groupe"=>'Aramis',"promo"=>'2022-2023',"user"=>'12102216',"mdp"=>'azerty'],
			["nom"=>'Desmarais',"prenom"=>'Geneviève',"mail"=>'desmarais.geneviere@gmail.com',"role"=>'e',"departement"=>'Informatique',"composante"=>'B.U.T. Informatique',"groupe"=>'Porthos',"promo"=>'2022-2023',"user"=>'12106008',"mdp"=>'azerty'],
			["nom"=>'Laframboise',"prenom"=>'Mélissa',"mail"=>'laframboise.melissa@gmail.com',"role"=>'e',"departement"=>'Informatique',"composante"=>'B.U.T. Informatique',"groupe"=>'Porthos',"promo"=>'2022-2023',"user"=>'12100952',"mdp"=>'azerty'],
			["nom"=>'Toutain',"prenom"=>'Jean-Charles',"mail"=>'toutain.jeancharles@gmail.com',"role"=>'e',"departement"=>'Informatique',"composante"=>'B.U.T. Passerelle',"groupe"=>'Passerelle',"promo"=>'2022-2023',"user"=>'12102241',"mdp"=>'azerty'],
			["nom"=>'De Verley',"prenom"=>'Michaël',"mail"=>'deverley.michael@gmail.com',"role"=>'e',"departement"=>'Informatique',"composante"=>'B.U.T. Passerelle',"groupe"=>'Passerelle',"promo"=>'2022-2023',"user"=>'12106433',"mdp"=>'azerty'],
			["nom"=>'Oui',"prenom"=>'Shérine',"mail"=>'oui.sherine@gmail.com',"role"=>'e',"departement"=>'Informatique',"composante"=>'B.U.T. STID',"groupe"=>'Staatskunde',"promo"=>'2022-2023',"user"=>'12108981',"mdp"=>'azerty'],
			["nom"=>'Baschet',"prenom"=>'Kévin',"mail"=>'baschet.kevin@gmail.com',"role"=>'e',"departement"=>'Informatique',"composante"=>'B.U.T. STID',"groupe"=>'Staatskunde',"promo"=>'2022-2023',"user"=>'12103630',"mdp"=>'azerty'],
			["nom"=>'Marais',"prenom"=>'Abel',"mail"=>'marais.abel@gmail.com',"role"=>'e',"departement"=>'Informatique',"composante"=>'B.U.T. GEII',"groupe"=>'Lavoisier',"promo"=>'2022-2023',"user"=>'12103418',"mdp"=>'azerty'],
			["nom"=>'Du Toit',"prenom"=>'Timothé',"mail"=>'dutoit.timothe@gmail.com',"role"=>'e',"departement"=>'Informatique',"composante"=>'B.U.T. GEII',"groupe"=>'Lavoisier',"promo"=>'2022-2023',"user"=>'12106390',"mdp"=>'azerty'],
			["nom"=>'Lemaître',"prenom"=>'Adolphe',"mail"=>'lemaitre.adolphe@gmail.com',"role"=>'e',"departement"=>'Informatique',"composante"=>'Licence Professionnelle MICDTL',"groupe"=>'Hammourabi',"promo"=>'2022-2023',"user"=>'12104559',"mdp"=>'azerty'],
			["nom"=>'Lecocq',"prenom"=>'Gilbert',"mail"=>'lecoq.gilbert@gmail.com',"role"=>'e',"departement"=>'Informatique',"composante"=>'Licence Professionnelle MICDTL',"groupe"=>'Hammourabi',"promo"=>'2022-2023',"user"=>'12107363',"mdp"=>'azerty'],
			["nom"=>'Swen',"prenom"=>'Christophe',"mail"=>'swen.christophe@gmail.com',"role"=>'e',"departement"=>'Informatique',"composante"=>'B.U.T. Réseaux & Télécommunications',"groupe"=>'Hooke',"promo"=>'2022-2023',"user"=>'12107718',"mdp"=>'azerty'],
			["nom"=>'Laforet',"prenom"=>'Silvine',"mail"=>'laforet.silvine@gmail.com',"role"=>'e',"departement"=>'Informatique',"composante"=>'B.U.T. Réseaux & Télécommunications',"groupe"=>'Hooke',"promo"=>'2022-2023',"user"=>'12108107',"mdp"=>'azerty'],
			["nom"=>'Baudelaire',"prenom"=>'Géraldine',"mail"=>'baudelaire.geraldine@gmail.com',"role"=>'e',"departement"=>'Électronique',"composante"=>'Licence Professionnelle MECSE',"groupe"=>'Pentode',"promo"=>'2022-2023',"user"=>'12109079',"mdp"=>'azerty'],
			["nom"=>'Gigot',"prenom"=>'Aimée',"mail"=>'gigot.aimee@gmail.com',"role"=>'e',"departement"=>'Électronique',"composante"=>'Licence Professionnelle MECSE',"groupe"=>'Pentode',"promo"=>'2022-2023',"user"=>'12109046',"mdp"=>'azerty'],
			["nom"=>'La Sueur',"prenom"=>'Angeline',"mail"=>'lasueur.angeline@gmail.com',"role"=>'e',"departement"=>'Réseaux/Télécoms',"composante"=>'B.U.T. GEII',"groupe"=>'Lavoisier',"promo"=>'2022-2023',"user"=>'12107804',"mdp"=>'azerty'],
			["nom"=>'Deschanel',"prenom"=>'Désirée',"mail"=>'deschanel.desiree@gmail.com',"role"=>'e',"departement"=>'Réseaux/Télécoms',"composante"=>'B.U.T. Informatique',"groupe"=>'Network',"promo"=>'2022-2023',"user"=>'12106672',"mdp"=>'azerty'],
			["nom"=>'Marais',"prenom"=>'Élisée',"mail"=>'marais.elisee@gmail.com',"role"=>'e',"departement"=>'Ressources Humaines',"composante"=>'B.U.T. GEA',"groupe"=>'Cantet',"promo"=>'2022-2023',"user"=>'12107102',"mdp"=>'azerty'],
			["nom"=>'Beaumont',"prenom"=>'Anne-Marie',"mail"=>'beaumont.annemarie@gmail.com',"role"=>'e',"departement"=>'Droit/Juridique',"composante"=>'B.U.T. Carrières Juridiques',"groupe"=>'Feuchère',"promo"=>'2022-2023',"user"=>'12109446',"mdp"=>'azerty'],
			["nom"=>'Secret',"prenom"=>'Victoria',"mail"=>'secret.victoria@gmail.com',"role"=>'e',"departement"=>'Droit/Juridique',"composante"=>'B.U.T. Carrières Juridiques',"groupe"=>'Feuchère',"promo"=>'2022-2023',"user"=>'12102950',"mdp"=>'azerty'],
			["nom"=>'Porte-boneur',"prenom"=>'Marinette',"mail"=>'porteboneur.marinette@gmail.com',"role"=>'e',"departement"=>'Droit/Juridique',"composante"=>'Licence Professionnelle Métiers du Notariat',"groupe"=>'Ambrosiano',"promo"=>'2022-2023',"user"=>'12106918',"mdp"=>'azerty'],
			["nom"=>'Boulle',"prenom"=>'Violette',"mail"=>'boulle.violette@gmail.com',"role"=>'e',"departement"=>'Droit/Juridique',"composante"=>'Licence Professionnelle Métiers du Notariat',"groupe"=>'Ambrosiano',"promo"=>'2022-2023',"user"=>'12101799',"mdp"=>'azerty'],
			["nom"=>'Noir',"prenom"=>'Ludovic',"mail"=>'noir.ludovic@gmail.com',"role"=>'e',"departement"=>'Assurance/Banque',"composante"=>'B.U.T. GEA',"groupe"=>'Cantet',"promo"=>'2022-2023',"user"=>'12101913',"mdp"=>'azerty'],
			["nom"=>'Côté',"prenom"=>'Xavier',"mail"=>'cote.xavier@gmail.com',"role"=>'e',"departement"=>'Assurance/Banque',"composante"=>'Licence Professionnelle ABF',"groupe"=>'Trapeza',"promo"=>'2022-2023',"user"=>'12103038',"mdp"=>'azerty'],
			["nom"=>'Carré',"prenom"=>'Racine',"mail"=>'carre.racine@gmail.com',"role"=>'e',"departement"=>'Assurance/Banque',"composante"=>'Licence Professionnelle ABF',"groupe"=>'Trapeza',"promo"=>'2022-2023',"user"=>'12108950',"mdp"=>'azerty'],
			["nom"=>'De Villepin',"prenom"=>'Jean-François',"mail"=>'devillepin.jeanfrancois@gmail.com',"role"=>'e',"departement"=>'Gestion des Entreprises',"composante"=>'Licence Professionnelle CP',"groupe"=>'Sumer',"promo"=>'2022-2023',"user"=>'12109741',"mdp"=>'azerty'],
			["nom"=>'Reynaud',"prenom"=>'Christophe-Victor',"mail"=>'reynaud.cvictor@gmail.com',"role"=>'Enseignant Validateur',"departement"=>'Assurance/Banque',"composante"=>'B.U.T. GEA',"user"=>'17495',"mdp"=>'azerty'],
			["nom"=>'Barbier',"prenom"=>'Aimée',"mail"=>'barbier.aimee@gmail.com',"role"=>'Enseignant Tuteur',"departement"=>'Électronique',"composante"=>'Licence Professionnelle MECSE',"user"=>'14819',"mdp"=>'azerty'],
			["nom"=>'Fontaine',"prenom"=>'Joseph',"mail"=>'fontaine.joseph@gmail.com',"role"=>'Enseignant Tuteur',"departement"=>'Informatique',"composante"=>'B.U.T. STID',"user"=>'13093',"mdp"=>'azerty'],
			["nom"=>'Berthelot',"prenom"=>'Célina-Sophie',"mail"=>'berthelot.csophie@gmail.com',"role"=>'Enseignant Validateur',"departement"=>'Informatique',"composante"=>'B.U.T. Informatique',"user"=>'11001',"mdp"=>'azerty']

		];
		foreach ($users as $user){
			if ($user['role']=='e'){	/*créer une vérif si le groupe est valide*/
				$chemin= "Document_Stage/".$user['user']."/";  //ETUDIANT ID A REMPLACER PAR $_SESSION[Student_ID]
				dirname($chemin);
				$f=mkdir($chemin,0700,true);
				dirname($chemin.'Bordereau_d-offre_de_stage/');
				$f=mkdir($chemin.'Bordereau_d-offre_de_stage/',0700,true);
				dirname($chemin.'CV/');
				$f=mkdir($chemin.'CV/',0700,true);
				dirname($chemin.'Lettre_de_Motivation/');
				$f=mkdir($chemin.'Lettre_de_Motivation/',0700,true);
				dirname($chemin.'Journal_de_Bord/');
				$f=mkdir($chemin.'Journal_de_Bord/',0700,true);
				dirname($chemin.'Mini_Rapport_de_Stage/');
				$f=mkdir($chemin.'Mini_Rapport_de_Stage/',0700,true);
				dirname($chemin.'Rapport_final/');
				$f=mkdir($chemin.'Rapport_final/',0700,true);
			}
							
			$m->userCreater($user);
			
		}
		
		$data=['title'=>'PA'];
		$this->render('accueil',$data);
		
	}

    public function upload_fichier($fichier,$typeDeFichier,$user){
		/* 
		Cette fonction permet l'ajout d'un document dans la base de donnée.
		Elle prend en paramètres le nom du fichier, 
			le type de document (CV,Lettre_de_motivation, etc..),
			et le Username de l'élève qui dépose un document.
		Cette fonction ne renvoie rien, elle ajoute un document 
			dans la base de donnée avec :
			- le Type donner par $typeDeDocument, 
			- le Student_ID, l'id de l'utilisateur, récupérer avec le $user,
			- l'URL, le nom du fichier, qui est donné par $fichier,
			- la version qui est obtenu en comptant le nombre de fichier du 
				même type, déposer par ce même utilisateur, déjà existant,
			- et le Date_heure, la date de l'envoie (l'heure est avec),
				qui est obtenue en faisant un date("Y-m-d H:i:s", $dt),
				$dt étant le temps donner par time(),
			et si le type de documentest Bordereau_d-offre_de_Stage 
			il ajoute aussi le document dans la Table BOS, en récuppérant, 
			au passage, le Document_ID du document venant d'être poster.
			
		La complexité de cette fonction est en O(1) car elle n'effectue pas requete complexe, elle est donc constante.
		*/ 
		$id=$this->getID($user);
		$version=$this->getNewVersion($user,$typeDeFichier);
		
		$dt = time();
		$date=date( "Y-m-d H:i:s", $dt );
		$req =$this->bd->prepare('INSERT INTO Document(Type,Student_ID,Date_heure,URL,version) value(:typeDeFichier,:id,:date,:fichier,:version)');
		$req->bindValue(':typeDeFichier',$typeDeFichier);
		$req->bindValue(':id',$id);
		$req->bindValue(':fichier',$fichier);
		$req->bindValue(":date",$date);
		$req->bindValue(":version",$version);
		$req ->execute();
		
		if ($typeDeDocument='Bordereau_d-offre_de_Stage'){
			$reqDocID=$this->bd->prepare("SELECT Document_ID AS Vers FROM Document WHERE Student_ID=:id AND Type=:typeDeFichier AND URL=:fichier AND version=:version AND Date_heure=:date");
			$reqDocID->bindValue(':typeDeFichier',$typeDeFichier);
			$reqDocID->bindValue(':id',$id);
			$reqDocID->bindValue(':fichier',$fichier);
			$reqDocID->bindValue(":date",$date);
			$reqDocID->bindValue(":version",$version);
			$reqDocID ->execute();
			$DocID=$reqDocID->fetchAll(PDO::FETCH_ASSOC);
			
			
			$reqBOS =$this->bd->prepare('INSERT INTO BOS(Document_ID,Status,BOS_Flag,Date_heure) value(:DocID,:statue,false,:date)');
			$reqBOS->bindValue(':DocID',$DocID[0]['Vers']);
			$reqBOS->bindValue(':statue',"RAS");
			$reqBOS->bindValue(":date",$date);
			$reqBOS ->execute();
		}
		return (bool) $req->rowCount();

	}
	
	public function nomDocExist($user,$type,$url){
		/*
		Cette fonction renvoie vrai ou faux (true/false) 
			selon si un document tester existe deja 
			dans la base de donnée.
		Pour faire cette vérification, elle prend en paramètre :
			- $user, qui est le Username de l'utilisateur qui 
				veut poster le document,
			- $type, qui est le type du document à poster,
			- $url, qui est le nom du document.
		Cette fonction vérifie donc si le document a poster existe.
			et donc pour cela utilise les différents paramètre,
			le $user sert a récupérer l'id de l'étudiant.
		La complexité de cette fonction est O(1) car elle effectue une seule requête à la base de données pour vérifier si un document existe en utilisant l'ID de l'utilisateur, 
		le type de document et l'URL du document comme critères de recherche. 
		Elle n'utilise pas de boucles ou d'opérations répétitives qui pourraient augmenter la complexité de la fonction. 

		*/ 
		$id=$this->getID($user);
		$reqDocID=$this->bd->prepare("SELECT COUNT(Document_ID) AS nb FROM Document WHERE Student_ID=:id AND Type=:type AND URL=:url");
		$reqDocID->bindValue(':type',$type);
		$reqDocID->bindValue(':id',$id);
		$reqDocID->bindValue(':url',$url);
		$reqDocID ->execute();
		$DocID=$reqDocID->fetchAll(PDO::FETCH_ASSOC);
		
		return $DocID[0]['nb']==1;
		
	}
	
	public function getNewVersion($user,$type){
		/*
		Cette fonction reccupère la nouvelle version, pour l'upload.
		Elle prend en paramètres le nom d'utilisateur, $user,
			et le type de document, $type.
		Elle compte le nombre de fichier du type présciser, 
			poster par l'utilisateur, et ajoute 1 a ce
			nombre afin de pouvoir changer de version.
			Si aucun fichier existe dans ce type,
			pour cet utilisateur, il met 1 (car il n'y en a pas).
		La complexité de cette fonction est O(1), car elle effectue une seule requête à la base de données.
 */
		$id=$this->getID($user);
		$req =$this->bd->prepare("SELECT COUNT(version) AS Vers FROM Document WHERE Student_ID=:id AND Type=:type");//recupere Username, à écrire
		$req->bindValue(":id",$id);
		$req->bindValue(":type",$type);
		$req ->execute();
		$m=$req->fetchAll(PDO::FETCH_ASSOC);
		
		return $m[0]["Vers"]+1;
	}
	
	
	
	public function last50($user){
		/*
		Cette fonction renvoie les 50 dernier document envoyer
			par un utilisateur précis, elle les renvoie 
			en ordre décroissant afin d'avoir les derniers
			document poster en premiers. Doit être utilisé pour un étudiant.
		Elle prend en paramètre le Username, le nom d'utilisateur 
			de l'étudiant, $user, et renvoie les donnée nécessaires des
			50 derniers documents poster par l'étudiant pour la page étudiante.
		La complexité de cette fonction est O(n) où n est le nombre de documents retournés par la requête. 
		Cette fonction effectue une seule requête à la base de données pour récupérer les informations des 50 derniers documents 
		d'un utilisateur donné en utilisant l'ID de l'utilisateur comme critère de recherche. 
		Elle utilise ensuite une boucle pour parcourir les résultats de la requête et les formater en un tableau d'informations.Elle retourne finalement ce tableau. 
	*/
		$id=$this->getID($user);
		
		$reqInfos =$this->bd->prepare("SELECT Type,Date_heure,URL,version FROM Document WHERE Student_ID=:id ORDER BY Date_heure DESC LIMIT 50");
		$reqInfos->bindValue(":id",$id);
		$reqInfos ->execute();
		$last50=$reqInfos->fetchAll(PDO::FETCH_ASSOC);
		$infos=[];
		$nom=$this->getNom($user);
		$prenom=$this->getPrenom($user);
		$pn=$prenom.' '.$nom;
		foreach($last50 as $info){
			$infos[]=[
				'type'=>$info['Type'],
				'personne'=>$pn,
				'date'=>$info['Date_heure'],
				'url'=>$info['URL'],
				'version'=>$info['version']
			];
			
		}
		return $infos;
	}
	
	
	
	
	public function derniersDoc(){
		/*
		Cette fonction renvoie un tableau ayant toute les données nécessaires 
			de tout les dernier documents envoyé par tout les étudiant, 
			pour l'affichage de la page enseignante
		Cette fonction ne prend aucun paramètres. Si il n'y a pas de 
			document posté le tableau est vide.
		La complexité de cette fonction est O(n) où n est le nombre de documents retournés par la requête. Cette fonction effectue une seule requête 
		à la base de données pour récupérer les informations de tous les documents triés par ordre décroissant de date d'ajout. 
		Elle utilise ensuite une boucle pour parcourir les résultats de la requête et pour obtenir des informations supplémentaires sur l'utilisateur qui a ajouté le document 
		en utilisant des fonctions comme getUser, getPrenom, getNom, getDepartement, getFormation. Elle retourne finalement un tableau contenant ces informations.
		 */
		$reqInfos =$this->bd->prepare("SELECT Type,Document_ID,Student_ID,Date_heure,URL,version FROM Document ORDER BY Date_heure DESC");
		$reqInfos ->execute();
		$Docs=$reqInfos->fetchAll(PDO::FETCH_ASSOC);
		$infos=[];
		foreach($Docs as $info){
			$user=$this->getUser($info['Student_ID'],'Etudiant');
			$personne=$this->getPrenom($user)." ".$this->getNom($user);
			$departement=$this->getDepartement($user);
			$composante=$this->getFormation($user);
			$infos[]=[
				'type'=>$info['Type'],
				'docID'=>$info['Document_ID'],
				'personne'=>$personne,
				'date'=>$info['Date_heure'],
				'url'=>$info['URL'],
				'version'=>$info['version'],
				'user'=>$user
			];
			
		}
		return $infos;
	}
	
	
	
	public function getM($user){
		/*
		Cette fonction renvoie le Mot de Passe hacher
			de l'utilisateur voulant ce connecter. 
		Elle prend en paramètre le Username, le nom d'utilisateur, 
			$user, et renvoie le Mot de Passe pour 
			tester la fonctionnalité de connexion.
		La complexité de cette fonction est O(1) car elle effectue une seule requête à la base de données pour 
		récupérer le mot de passe haché de l'utilisateur en utilisant le nom d'utilisateur comme critère de recherche.
		 */
		$req =$this->bd->prepare("SELECT Password FROM Login WHERE Username=:user");
		$req->bindValue(":user",$user);
		$req ->execute();
		$m=$req->fetchAll(PDO::FETCH_ASSOC);
		
		return $m[0]["Password"];
	}
	
	public function getID($user){ 
		/*
		Cette fonction renvoie l'ID de l'utilisateur. 
		Elle prend en paramètre le Username, le nom d'utilisateur,
			 $user, et renvoie l'ID de l'utilisateur.
		 La complexité de cette fonction est O(1) car elle effectue une seule requête à la base de données pour récupérer 
		 l'ID de l'utilisateur en utilisant le nom d'utilisateur comme critère de recherche.
		  */
		$reqID =$this->bd->prepare("SELECT User_ID FROM Login WHERE Username=:user");
		$reqID->bindValue(":user",$user);
		$reqID ->execute();
		$mid=$reqID->fetchAll(PDO::FETCH_ASSOC);
		
		return $mid[0]["User_ID"];
	}
	
	
	

	public function getUser($id,$role){
		/*
		Cette fonction renvoie le Username, soit le nom d'utilisateur apparessant dans Login. 
		Elle prend en paramètre l'ID de l'utilisateur, le nom d'utilisateur, 
			$user, et le Rôle de cette utilisateur,
			et renvoie son Username.
		La complexité de cette fonction est O(1) car elle effectue une seule requête à la base de données pour récupérer le nom d'utilisateur en utilisant 
		l'ID de l'utilisateur et le rôle comme critères de recherche. 
		*/
		
		if ($role=='Etudiant'){
			$reqUser =$this->bd->prepare("SELECT Username FROM Login WHERE User_ID=:id AND Rôle=true");
		}
		else{
			$reqUser =$this->bd->prepare("SELECT Username FROM Login WHERE User_ID=:id AND Rôle=false");
		}
		$reqUser->bindValue(":id",$id);
		$reqUser ->execute();
		$mid=$reqUser->fetchAll(PDO::FETCH_ASSOC);
		
		return $mid[0]["Username"];
	}
	
	
	public function roleTable($user) {
		/*
		Cette fonction renvoie un tableau contennant le rôle de l'utilisateur
			et le nom de l'ID a utilisé selon le rôle.
			L'utilisateur entrer en paramètre afin de pouvoir des tests rapidement
			dans d'autre fonction du Model. 
		Elle prend en paramètre le Username, le nom d'utilisateur, $user.
		La complexité de cette fonction est O(1) car elle effectue une seule requête à la base de données pour récupérer
		 le rôle de l'utilisateur en utilisant le nom d'utilisateur comme critère de recherche. 
		*/
		$reqRole =$this->bd->prepare("SELECT Rôle FROM Login WHERE Username=:user");//recupere Username, à vérifier
		$reqRole ->bindValue(":user",$user);
		$reqRole ->execute();
		$role=$reqRole->fetchAll(PDO::FETCH_ASSOC);
		if ($role[0]["Rôle"]){//if true, true represente l'Etudiant
			$roleNom="Etudiant";//dans quel tab on va selon le rôle
			$roleID="Student_ID";//quel est l'id de la personne
		} else{
			$roleNom="Personnel";
			$roleID="Personnel_ID";
		}
        $data[]=$roleNom;
        $data[]=$roleID;
        return $data;
		
	}
	
	
	
	
	public function getNom($user) {
		/*
		Cette fonction renvoie le Nom de l'utilisateur. 
		Elle prend en paramètre le Username, le nom d'utilisateur,
			$user, et renvoie le Nom de l'utilisateur, en vérifiants
			son rôle d'avance.
		La complexité de cette fonction est O(1) car elle utilise d'abord la fonction roleTable qui est de complexité O(1) pour déterminer 
		le nom de la table à utiliser pour récupérer le nom de l'utilisateur.
		Elle effectue ensuite une seule requête à la base de données pour récupérer le nom de l'utilisateur en utilisant le nom d'utilisateur comme critère de recherche.
		*/
		$info=$this->roleTable($user);
		
		if ($info[0]=="Etudiant"){
			$reqNom=$this->bd->prepare("SELECT Nom FROM Login JOIN Etudiant ON User_ID=Student_ID WHERE Username=:user");//recupere Username, à écrire
		}
		elseif ($info[0]=="Personnel"){
			$reqNom=$this->bd->prepare("SELECT Nom FROM Login JOIN Personnel ON User_ID=Personnel_ID WHERE Username=:user");
		}
		
		$reqNom ->bindValue(":user",$user);
		$reqNom->execute();
		$nom=$reqNom->fetchAll(PDO::FETCH_ASSOC);
		
		return $nom[0]["Nom"];
	}
	
	
	
	public function getPrenom($user) {
		/*
		Cette fonction renvoie le Prenom de l'utilisateur.
		Elle prend en paramètre le Username, le nom d'utilisateur, $user, 
			et renvoie le Prenom de l'utilisateur, en vérifiants son rôle d'avance.
		La complexité de cette fonction est O(1) car elle utilise d'abord la fonction roleTable qui est de complexité O(1) 
		pour déterminer le nom de la table à utiliser pour récupérer le prénom de l'utilisateur. 
		Elle effectue ensuite une seule requête à la base de données pour récupérer le prénom de l'utilisateur en utilisant le nom d'utilisateur comme critère de recherche.

		*/ 
		$info=$this->roleTable($user);
		
		if ($info[0]=="Etudiant"){
			$reqPrenom=$this->bd->prepare("SELECT Prenom FROM Login JOIN Etudiant ON User_ID=Student_ID WHERE Username=:user");//recupere Username, à écrire
		}
		elseif ($info[0]=="Personnel"){
			$reqPrenom=$this->bd->prepare("SELECT Prenom FROM Login JOIN Personnel ON User_ID=Personnel_ID WHERE Username=:user");
		}
		
		$reqPrenom ->bindValue(":user",$user);
		$reqPrenom ->execute();
		$prenom=$reqPrenom->fetchAll(PDO::FETCH_ASSOC);
		
		return $prenom[0]["Prenom"];
	}
	
	
	
	public function getRole($user) {
		/*
		Cette fonction renvoie le Rôle de l'utilisateur. 
		Elle prend en paramètre le Username, le nom d'utilisateur, $user, 
			et renvoie le Nom de l'utilisateur, en vérifiants son rôle d'avance. 
			Soit Étudiant soit un Rôle de la table Personnel.
		La complexité de cette fonction est O(1) car elle utilise d'abord la fonction roleTable qui est de complexité O(1) 
		pour déterminer le nom de la table à utiliser pour récupérer le rôle de l'utilisateur. 
		*/
		$info=$this->roleTable($user);
		
		if ($info[0]=='Etudiant'){
			return 'Étudiant';
			
		}
		else{
			$reqRole=$this->bd->prepare("SELECT Personnel.Rôle FROM Login JOIN Personnel ON User_ID=Personnel_ID WHERE Username=:user");//recupere Username, à écrire
			$reqRole ->bindValue(":user",$user);
			$reqRole ->execute();
			$role=$reqRole->fetchAll(PDO::FETCH_ASSOC);
			
			return $role[0]["Rôle"];
		}
	}
	
	
	
	public function getMail($user) {
		/*
		Cette fonction renvoie le Mail de l'utilisateur. 
		Elle prend en paramètre le Username, le nom d'utilisateur, $user, 
			et renvoie le Mail de l'utilisateur, en vérifiants son rôle d'avance. 
		La complexité de cette fonction est O(1) car elle utilise d'abord la fonction roleTable qui est de complexité O(1) 
		pour déterminer le nom de la table à utiliser pour récupérer l'adresse mail de l'utilisateur. Elle effectue ensuite une seule requête à la base de données 
		pour récupérer l'adresse mail de l'utilisateur en utilisant le nom d'utilisateur comme critère de recherche.
		*/
		$info=$this->roleTable($user);
		
		if ($info[0]=="Etudiant"){
			$reqMail=$this->bd->prepare("SELECT Mail FROM Login JOIN Etudiant ON User_ID=Student_ID WHERE Username=:user");//recupere Username
		}
		elseif ($info[0]=="Personnel"){
			$reqMail=$this->bd->prepare("SELECT Mail FROM Login JOIN Personnel ON User_ID=Personnel_ID WHERE Username=:user");//recupere Username
		}
		
		$reqMail ->bindValue(":user",$user);
		$reqMail ->execute();
		$mail=$reqMail->fetchAll(PDO::FETCH_ASSOC);
		
		return $mail[0]["Mail"];
	}
	
	
	public function getDepartement($user) {
		/*
		Cette fonction renvoie le Département de l'utilisateur.
		Elle prend en paramètre le Username, le nom d'utilisateur, $user, 
			et renvoie le Département de l'utilisateur, en vérifiants son rôle d'avance.
		La complexité de cette fonction est O(2) car elle utilise d'abord la fonction roleTable qui est de complexité O(1) 
		pour déterminer le rôle de l'utilisateur et donc la table à utiliser pour récupérer le département de l'utilisateur. 
		Elle effectue ensuite deux requêtes à la base de données : une pour récupérer l'ID de la formation de l'utilisateur et l'autre pour récupérer le département 
		de cette formation en utilisant l'ID de la formation comme critère de recherche.
 */
		$info=$this->roleTable($user);
		
		if ($info[0]=="Etudiant"){
			$reqG_ID=$this->bd->prepare("SELECT Groupe_ID FROM Login JOIN Etudiant ON User_ID=Student_ID WHERE Username=:user");//recupere Username, à écrire
			
			$reqG_ID ->bindValue(":user",$user);
			$reqG_ID ->execute();
			$G_ID=$reqG_ID->fetchAll(PDO::FETCH_ASSOC);
			$reqF_ID=$this->bd->prepare("SELECT Formation_ID FROM Groupe WHERE Groupe_ID=:G_ID");
			$reqF_ID ->bindValue(":G_ID",$G_ID[0]['Groupe_ID']);
			$reqF_ID ->execute();
			
			
		}
		elseif ($info[0]=="Personnel"){
			$reqF_ID=$this->bd->prepare("SELECT Formation_ID FROM Login JOIN Personnel ON User_ID=Personnel_ID WHERE Username=:user");//recupere Username, à écrire
			$reqF_ID ->bindValue(":user",$user);
			$reqF_ID ->execute();
		}
		
		
		$F_ID=$reqF_ID->fetchAll(PDO::FETCH_ASSOC);
		
		$reqFormation=$this->bd->prepare("SELECT Département FROM Formation WHERE Formation_ID=:F_ID");
		$reqFormation ->bindValue(":F_ID",$F_ID[0]["Formation_ID"]);
		$reqFormation ->execute();
		$Formation=$reqFormation->fetchAll(PDO::FETCH_ASSOC);
		
		return $Formation[0]["Département"];
	}

	
	
	public function getFormation($user) {
		/*
		Cette fonction renvoie la Composante de l'utilisateur.
		Elle prend en paramètre le Username, le nom d'utilisateur, $user, 
			et renvoie la Composante de l'utilisateur, en vérifiants son rôle d'avance.
		La complexité de cette fonction est de O(1) pour la fonction roleTable() et de O(n) pour les requêtes SQL qui sont effectuées ensuite. 
		Cela signifie qu'elle est linéaire par rapport à la taille des données dans la base de données. 
 */
		$info=$this->roleTable($user);
		
		if ($info[0]=="Etudiant"){
			$reqG_ID=$this->bd->prepare("SELECT Groupe_ID FROM Login JOIN Etudiant ON User_ID=Student_ID WHERE Username=:user");//recupere Username, à écrire
			
			$reqG_ID ->bindValue(":user",$user);
			$reqG_ID ->execute();
			$G_ID=$reqG_ID->fetchAll(PDO::FETCH_ASSOC);
			$reqF_ID=$this->bd->prepare("SELECT Formation_ID FROM Groupe WHERE Groupe_ID=:G_ID");
			$reqF_ID ->bindValue(":G_ID",$G_ID[0]['Groupe_ID']);
			$reqF_ID ->execute();
			
			
		}
		elseif ($info[0]=="Personnel"){
			$reqF_ID=$this->bd->prepare("SELECT Formation_ID FROM Login JOIN Personnel ON User_ID=Personnel_ID WHERE Username=:user");//recupere Username, à écrire
			$reqF_ID ->bindValue(":user",$user);
			$reqF_ID ->execute();
		}
		
		
		$F_ID=$reqF_ID->fetchAll(PDO::FETCH_ASSOC);
		
		$reqFormation=$this->bd->prepare("SELECT Composante FROM Formation WHERE Formation_ID=:F_ID");
		$reqFormation ->bindValue(":F_ID",$F_ID[0]["Formation_ID"]);
		$reqFormation ->execute();
		$Formation=$reqFormation->fetchAll(PDO::FETCH_ASSOC);
		
		return $Formation[0]["Composante"];
	}




	public function formationValide($dep,$comp){
		/*
		Cette fonction vérifie si une formation donner existe.
		Elle prend en paramètre le Département, $dep, et la Composante, $comp,
			et vérifie la validité de la Formation avec la base de données.
		Renvoie true si existe, false sinon.
		La complexité de cette fonction est O(1) car elle effectue une seule requête SQL qui compte le nombre de lignes dans la table "Formation" qui correspondent 
		aux valeurs spécifiées pour les colonnes "Département" et "Composante", puis retourne un résultat booléen indiquant si le nombre de lignes correspondantes est égal à 1
 */
		$reqFormationV=$this->bd->prepare("SELECT COUNT(Formation_ID) AS nb FROM Formation WHERE Département=:dep AND Composante=:comp");
		$reqFormationV ->bindValue(":dep",$dep);
		$reqFormationV ->bindValue(":comp",$comp);
		$reqFormationV ->execute();
		$FormationV=$reqFormationV->fetchAll(PDO::FETCH_ASSOC);
		return $FormationV[0]['nb']==1;
	}


	public function groupeValide($groupe,$dep,$comp){
		/*
		Cette fonction vérifie si une formation donner existe.
		Elle prend en paramètre le Département, $dep,
			la Composante, $comp,
			et le Groupe, $groupe,
			et vérifie la validité de la Formation, avec son groupe, avec la base de données.
		Renvoie true si existe, false sinon. 
		La complexité de cette fonction est linéaire, car elle effectue des opérations de base (préparer et exécuter une requête, 
		lier des valeurs, compter les résultats) qui sont toutes en O(n), où n est le nombre de lignes retournées par la requête SQL. 
		La fonction appelle également une autre fonction "formationValide" qui est également en complexité linéaire
		*/
		if($this->formationValide($dep,$comp)){
			$reqGroupeV=$this->bd->prepare("SELECT COUNT(Groupe_ID) AS nb FROM Groupe JOIN Formation ON Formation_ID WHERE Nom=:groupe AND Département=:dep AND Composante=:comp");
			$reqGroupeV ->bindValue(":groupe",$groupe);
			$reqGroupeV ->bindValue(":dep",$dep);
			$reqGroupeV ->bindValue(":comp",$comp);
			$reqGroupeV ->execute();
			$GroupeV=$reqGroupeV->fetchAll(PDO::FETCH_ASSOC);
			return $GroupeV[0]['nb']==1;
		}
	}

	public function takeDepartements(){
		/* 
		Cette fonction renvoie un tableau contenant les Départements existants.
		Elle prend aucun paramètres.
		Cette fonction est utilisé pour la création de Composantes, de Groupes, et l'inscription.
		La complexité de cette fonction est de O(n) où n est le nombre de départements différents dans la table Formation. 
		La requête SQL est en O(m) où m est le nombre de lignes de la table Formation et la boucle foreach est en O(n). 
		*/ 
		$reqDep=$this->bd->prepare("SELECT DISTINCT Département FROM Formation");
		$reqDep ->execute();
		$Dep=$reqDep->fetchAll(PDO::FETCH_ASSOC);
		$Departements=[];
		foreach ($Dep as $dep){
			$Departements[]=$dep['Département'];
		}
		return $Departements;
		
	}
	
	
	public function takeComposantes($departement){
		/* 
		Cette fonction renvoie un tableau contenant les Composantes existantes. 
		Elle prend en paramètre le Département, $département et renvoie un tableau de Composantes.
		On avait pas expliquer a notre groupe ce qu'étais une Composante,
			ou un Département donc on a chercher 
			et penser que une Composante etait une composante du département 
			tel que le BUT INFO était pour nous une composante du Département Informatique.
		Cette fonction est utilisé pour la création de Groupes, et l'inscription.
		La complexité de ces deux fonctions est en O(n), où n est le nombre de départements ou de composantes dans la table de formation. Cela est dû au fait 
		que les deux fonctions utilisent une boucle pour parcourir tous les enregistrements 
		de la table de formation et stocker les valeurs distinctes de Département ou Composante dans un tableau.
		*/ 
		$reqComp=$this->bd->prepare("SELECT DISTINCT Composante FROM Formation WHERE Département=:dep");
		$reqComp ->bindValue(":dep",$departement);
		$reqComp ->execute();
		$Comp=$reqComp->fetchAll(PDO::FETCH_ASSOC);
		foreach ($Comp as $dep){
			$Composantes[]=$dep['Composante'];
		}
		return $Composantes;
	}
	
	
	public function takeGroupes($departement,$composante){
		/*
		Cette fonction renvoie un tableau contenant les Groupes existants. 
		Elle prend en paramètre le Département, $département,
			et la Composante, $composante
			et renvoie un tableau de Groupes.
		Cette fonction est utilisé pour l'inscription.
		La complexité de cette fonction est également de O(n), où n est le nombre de groupes correspondant aux critères de département
		et de composante donnés dans la requête SQL. 
		*/
		$reqGroup=$this->bd->prepare("SELECT DISTINCT Nom FROM Groupe JOIN Formation ON Groupe.Formation_ID=Formation.Formation_ID WHERE Département=:dep AND Composante=:comp");
		$reqGroup ->bindValue(":dep",$departement);
		$reqGroup ->bindValue(":comp",$composante);
		$reqGroup ->execute();
		$Group=$reqGroup->fetchAll(PDO::FETCH_ASSOC);
		$groupes=[];
		foreach ($Group as $group){
			$groupes[]=$group['Nom'];
		}
		return $groupes;
		
	}
	
	public function takePromos($departement,$composante,$groupe){
		/*
		Cette fonction renvoie un tableau contenant les Promos (ex de promo:2023-2024) existantes. 
		Elle prend en paramètre le Département, $département,
			la Composante, $composante,
			et le Groupes, $groupes et renvoie un tableau de Promos. 
		Cette fonction est utilisé pour l'inscription. 
		La complexité de cette fonction est également de O(n), où n est le nombre de promotions correspondant aux critères de département, 
		de composante et de groupe donnés dans la requête SQL. 
		*/
		$reqPromos=$this->bd->prepare("SELECT Promotion FROM Groupe JOIN Formation ON Groupe.Formation_ID=Formation.Formation_ID WHERE Département=:dep AND Composante=:comp AND Nom=:groupe");
		$reqPromos ->bindValue(":dep",$departement);
		$reqPromos ->bindValue(":comp",$composante);
		$reqPromos ->bindValue(":groupe",$groupe);
		$reqPromos ->execute();
		$Promo=$reqPromos->fetchAll(PDO::FETCH_ASSOC);
		$promos=[];
		foreach ($Promo as $promo){
			$promos[]=$promo['Promotion'];
		}
		return $promos;
		
	}
	
	
	
	public function takeCommentaires($docID){
		/*
		Cette fonction renvoie un tableau contenant les Commentaire existants. 
		Elle prend en paramètre l'ID du Documents et renvoie 
			un tableau de Commentaires en décroissant, donc du plus récent au plus loingtain.
		Cette fonction est utilisé pour la page de l'enseignant, afin d'afficher les commentaires envoyés.
		La complexité de cette fonction est de O(n) * O(m), où n est le nombre de commentaires 
		correspondant à l'ID du document donné dans la requête SQL, et où m est la complexité de la fonction getUser().
		 */
		$reqComs=$this->bd->prepare("SELECT Commentaire_ID,Personnel_ID,Commentaire FROM Commentaire WHERE Document_ID=:docID ORDER BY Commentaire_ID DESC");
		$reqComs ->bindValue(":docID",$docID);
		$reqComs ->execute();
		$Coms=$reqComs->fetchAll(PDO::FETCH_ASSOC);
		$commentaires=[];
		foreach ($Coms as $com){
			$user=$this->getUser($com['Personnel_ID'],'Autre');
			$personne=$this->getPrenom($user)." ".$this->getNom($user);
			$commentaires[]=["personne"=>$personne,"commentaire"=>$com['Commentaire'],'user'=>$user,"comID"=>$com['Commentaire_ID']];
		}
		return $commentaires;
		
	}
	
	
	
	
	public function userExist($user){
		/*
		Cette fonction vérifie si un utilisateur envoyé en paramètre existe, et renvoie un tableau contenant les informations nécéssaire 
			a la connexion, et donc à la session. 
			Si il n'existe pas, la fonction renvoie false. 
		Elle prend en paramètre un nom d'utilisateur, $user.
		La complexité de cette fonction est de O(1) pour la requête SQL qui vérifie si l'utilisateur existe, suivie de O(m) pour la requête SQL 
		pour récupérer les informations de l'utilisateur si il existe, où m est le nombre d'informations récupérées pour l'utilisateur.
		
		*/ 
		$req =$this->bd->prepare("SELECT COUNT(Username) AS nb FROM Login WHERE Username=:user");
		$req->bindValue(":user",$user);
		$req ->execute();
		$t=$req->fetchAll(PDO::FETCH_ASSOC);
		if ($t[0]["nb"]==1){
			$data=[];
			$nomPersonne=$this->getNom($user);
			$prenomPersonne=$this->getPrenom($user);
			
			$data["nomPersonne"]=$nomPersonne;
			$data["prenomPersonne"]=$prenomPersonne;
			$data["personne"]=np($nomPersonne,$prenomPersonne);
			$data["role"]=$this->getRole($user);
			$data["n"]=$user;//n pour le prenom et nom
			$data["mail"]=$this->getMail($user);
			$data["Formation"]=$this->getFormation($user);
			
			return $data;
		}
		return false;
	}
	
	
	public function personneExist($data){
		/*
		Cette fonction vérifie si un utilisateur envoyé dans le tableau en paramètre existe, 
			et renvoie true si il existe, si les informations de la session sont bonnes
			(le tableau en paramètre contien les informations de la session).
		Elle prend en paramètre un tableau contenant les informations d'une session existante.
		La complexité de cette fonction est de O(1) pour la requête SQL qui vérifie si la personne existe.
		*/ 
		$role=$data['role'];
		
		if ($role=='e'){
			$req =$this->bd->prepare("SELECT COUNT(Nom) AS nb FROM Etudiant WHERE Nom=:nom AND Prenom=:prenom AND Mail=:mail");
			
		}
		
		else {
			$req =$this->bd->prepare("SELECT COUNT(Nom) AS nb FROM Personnel WHERE Nom=:nom AND Prenom=:prenom AND Mail=:mail AND Rôle=:role");
			$req->bindValue(":role",$role);
		
		}
		
		$req->bindValue(":nom",$data['role']);
		$req->bindValue(":prenom",$data['role']);
		$req->bindValue(":mail",$data['role']);
		$req ->execute();
		$t=$req->fetchAll(PDO::FETCH_ASSOC);
		
		return $t[0]['nb']==1;
		
	}
	
	
	public function ajoutComp($departement,$composante){
		/*
		Cette fonction insert dans la base de données une Formation.
		Elle prend en paramètre un Département, $departement,et une Composante, $composante.
		Cette fonction est utilisée dans l'inscription.
		La complexité de cette fonction est de O(1) pour l'insertion de la composante dans la table Formation.
		*/
		$reqAjComp=$this->bd->prepare("INSERT INTO Formation(Département,Composante) value(:dep,:comp)");
		$reqAjComp ->bindValue(":dep",$departement);
		$reqAjComp ->bindValue(":comp",$composante);
		$reqAjComp -> execute();
	}
	
	
	
	public function ajoutGroupe($departement,$composante,$infos){
		/* 
		Cette fonction insert dans la base de données un Groupe.Elle prend en paramètre un Département, $departement,une Composante, $composante
			et un tableau contenant les informations du Groupe, $info.
			Cette fonction est utilisée dans l'inscription.
		La complexité de cette fonction est de O(1) pour la première requête SQL qui récupère l'ID de la formation
		 correspondant aux critères de département et de composante donnés, suivie de O(1) pour la seconde requête SQL qui ajoute le groupe à la table Groupe en utilisant 
		l'ID de la formation récupéré ainsi que les informations de promotion, de nom de groupe et de niveau données. 
		
		*/ 
		$reqIDFormation=$this->bd->prepare("SELECT Formation_ID FROM Formation WHERE Département=:dep AND Composante=:comp");
		$reqIDFormation->bindValue(':dep',$departement);
		$reqIDFormation->bindValue(':comp',$composante);
		$reqIDFormation ->execute();
		$F_ID=$reqIDFormation->fetchAll(PDO::FETCH_ASSOC);//reccupère l'ID de la formation
		
		$reqAjGroup=$this->bd->prepare("INSERT INTO Groupe(Formation_ID,Promotion,Nom,Niveau) value(:F_ID,:promo,:nom,:niveau)");
		$reqAjGroup ->bindValue(":F_ID",$F_ID[0]['Formation_ID']);
		$reqAjGroup ->bindValue(":promo",$infos['promo']);
		$reqAjGroup ->bindValue(":nom",$infos['groupe']);
		$reqAjGroup ->bindValue(":niveau",$infos['niveau']);
		$reqAjGroup -> execute();
	}
	
	
	public function ajoutCommentaire($docID,$user,$commentaire){
		/*
		Cette fonction insert dans la base de données un Commentaire.
		Elle prend en paramètre l'ID du Document ou est mis le Commentaire, $docID,
			le nom d'utilisateur de la personne qui l'envoie, $user,
			et le commentaire qu'il a écrit.
		Cette fonction est utilisée dans la fonctionnalité commentaire, pour l'envoie de commentaire.
		La complexité de cette fonction est de O(1) pour l'insertion d'un commentaire dans la table Commentaire, cela est dû au fait que la fonction effectue une seule requête 
		d'insertion en utilisant les valeurs de l'ID de l'utilisateur, de l'ID du document et du contenu du commentaire pour ajouter une nouvelle entrée à la table.
		La fonction utilise également la fonction getID() qui a une complexité O(1) pour récupérer l'ID de l'utilisateur.

	*/ 
		$id=$this->getID($user);
		$reqAjCom=$this->bd->prepare("INSERT INTO Commentaire(Personnel_ID,Document_ID,Commentaire,Visibility_flag,Vue_flag) value(:id,:docID,:com,true,false)");
		$reqAjCom ->bindValue(":id",$id);
		$reqAjCom ->bindValue(":docID",$docID);
		$reqAjCom ->bindValue(":com",$commentaire);
		$reqAjCom -> execute();
		
		
		
	}
	
	
	public function userCreater($data){
		/*	
		Cette fonction créer un utilisateur.Elle prend en paramètre un tableau contenant toutes les informations 
			nécessaires a la création d'un utilisateur.
			Qu'il soit étudiant ou autre. Cette fonction est utilisée dans l'inscription.
        La complexité de cette fonction est O(1) car elle récupère des informations précise d'un tableau.
        Aucune boucle ne figure dans la fonction , plusieurs vérifications sont faites.
		
		*/
		$reqIDFormation=$this->bd->prepare("SELECT Formation_ID FROM Formation WHERE Département=:dep AND Composante=:comp");
		$reqIDFormation->bindValue(':dep',$data['departement']);
		$reqIDFormation->bindValue(':comp',$data['composante']);
		$reqIDFormation ->execute();
		$F_ID=$reqIDFormation->fetchAll(PDO::FETCH_ASSOC);//reccupère l'ID de la formation
		
		
		if ($data['role']=='e'){
			$reqIDGroupe=$this->bd->prepare("SELECT Groupe_ID FROM Groupe WHERE Nom=:groupe AND Formation_ID=:formation AND Promotion=:promo");
			$reqIDGroupe->bindValue(':groupe',$data['groupe']);
			$reqIDGroupe->bindValue(':formation',$F_ID[0]['Formation_ID']);
			$reqIDGroupe->bindValue(':promo',$data['promo']);
			$reqIDGroupe->execute();
			$G_ID=$reqIDGroupe->fetchAll(PDO::FETCH_ASSOC);//reccupère l'ID du groupe
			
			
			$reqCreateP=$this->bd->prepare("INSERT INTO Etudiant(Nom,Prenom,Mail,Stage_detention,Visibility_flag,Groupe,Groupe_ID) value(:nom,:prenom,:mail,false,false,:groupe,:groupeID)");
			$reqCreateP->bindValue(':nom',$data['nom']);
			$reqCreateP->bindValue(':prenom',$data['prenom']);
			$reqCreateP->bindValue(':mail',$data['mail']);
			$reqCreateP->bindValue(':groupe',$data['groupe']);
			$reqCreateP->bindValue(':groupeID',$G_ID[0]['Groupe_ID']);
			$reqCreateP ->execute();//Creer la personne dans la table Etudiant
			
			$reqUserID=$this->bd->prepare("SELECT Student_ID FROM Etudiant WHERE Nom=:nom AND Prenom=:prenom AND Mail=:mail AND Groupe=:groupe AND Groupe_ID=:groupeID");
			$reqUserID->bindValue(':nom',$data['nom']);
			$reqUserID->bindValue(':prenom',$data['prenom']);
			$reqUserID->bindValue(':mail',$data['mail']);
			$reqUserID->bindValue(':groupe',$data['groupe']);
			$reqUserID->bindValue(':groupeID',$G_ID[0]['Groupe_ID']);
			$reqUserID -> execute();
			$S_ID= $reqUserID->fetchAll(PDO::FETCH_ASSOC);//reccupère l'ID de l'Etudiant
			$s_ID=$S_ID[0]['Student_ID'];
			
			$reqCreateL=$this->bd->prepare("INSERT INTO Login(Username,Password,User_ID,Rôle) value(:user,:mdp,:uid,true)");
			$reqCreateL->bindValue(':user',$data['user']);
			$reqCreateL->bindValue(':mdp',password_hash($data['mdp'], PASSWORD_DEFAULT));
			$reqCreateL->bindValue(':uid',$s_ID);
			$reqCreateL ->execute();//Creer l'utilisateur
		}
		
		elseif ($data['role']=='Enseignant Tuteur' ||  $data['role']=='Enseignant Validateur' || $data['role']=='Membre du Secrétariat' || $data['role']=='Coordinatrice de stage') {
			$reqCreateP=$this->bd->prepare("INSERT INTO Personnel(Nom,Prenom,Mail,Visibility_flag,Rôle,Formation_ID) value(:nom,:prenom,:mail,false,:role,:formationID)");
			$reqCreateP->bindValue(':nom',$data['nom']);
			$reqCreateP->bindValue(':prenom',$data['prenom']);
			$reqCreateP->bindValue(':mail',$data['mail']);
			$reqCreateP->bindValue(':role',$data['role']);
			$reqCreateP->bindValue(':formationID',$F_ID[0]['Formation_ID']);
			$reqCreateP ->execute();//Creer la personne dans la table Personnel
			
			$reqUserID=$this->bd->prepare("SELECT Personnel_ID FROM Personnel WHERE Nom=:nom AND Prenom=:prenom AND Mail=:mail AND Rôle=:role AND Formation_ID=:formationID");
			$reqUserID->bindValue(':nom',$data['nom']);
			$reqUserID->bindValue(':prenom',$data['prenom']);
			$reqUserID->bindValue(':mail',$data['mail']);
			$reqUserID->bindValue(':role',$data['role']);
			$reqUserID->bindValue(':formationID',$F_ID[0]['Formation_ID']);
			$reqUserID -> execute();
			$S_ID= $reqUserID->fetchAll(PDO::FETCH_ASSOC);//reccupère l'ID du personnel
			$s_ID=$S_ID[0]['Personnel_ID'];
			
			
			$reqCreateL=$this->bd->prepare("INSERT INTO Login(Username,Password,User_ID,Rôle) value(:user,:mdp,:uid,false)");
			$reqCreateL->bindValue(':user',$data['user']);
			$reqCreateL->bindValue(':mdp',password_hash($data['mdp'], PASSWORD_DEFAULT));
			$reqCreateL->bindValue(':uid',$s_ID);
			$reqCreateL ->execute();//Creer l'utilisateur
		}
		
	}	
		
    function e($message)
{
    return htmlspecialchars($message, ENT_QUOTES);
}


function formation($i){
	/*
	N'est pas utilisé !
	Mais été censé être utiliser dans l'inscription. 
	La complexité de cette fonction est de O(1), car elle effectue un nombre constant d'opérations (vérification de condition) indépendamment de la taille de l'entrée.
	Elle retourne une seule valeur en fonction de la valeur de l'entrée, qui est comparée à 0 et 1.
	*/
	if ($i==0){
		$formation="BUT 1ère année";
	}
	
	elseif ($i==1){
		$formation="BUT 2ème année";
	}
	
	else {
		$formation="Il n'y a pas de formation enregistré";
	}
	
	return $formation;
}

function pdate($date){
	/* 
	Cette fonction renvoie au format d'un texte la date, reçu de la base de donnée, en paramètre.
	Si $date="2022-12-31 14:43:00" alors le retour est "31 décembre 2022, 14:43".
	La complexité de cette fonction est de O(1), car elle effectue un nombre constant d'opérations (découpage de chaînes, 
	accès à un tableau et ce indépendamment de la taille de l'entrée.
	*/
	$mois=["janvier","février","mars","avril","mai","juin","juillet","août","septembre","octobre","novembre","décembre"];
	$tout=explode(" ",$date);
	$jour=explode("-",$tout[0]);
	$heure=explode(":",$tout[1]);
	return "".$jour[2]." ".$mois[$jour[1]-1]." ".$jour[0].", ".$heure[0].":".$heure[1];
 }

function np($nom,$prenom){
	/* 
	Cette fonction renvois les deux chaines entrer en paramètre concaténer.
	La complexité de cette fonction est O(1), elle ne dépend pas de la taille des entrées,
	 elle prend simplement deux variables, nom et prénom, et les concatène pour créer une chaine de caractères "Prenom Nom"
	*/
	return e($prenom)." ".e($nom);
}

function sessionValide($session){
	/* 
	Cette fonction vérifie que le tableau de la session 
		envoyé en paramètre contient bien les bons paramètres.
	La complexité de cette fonction est en O(1), car elle effectue un certain nombre de vérifications (isset) sur les champs nomPersonne, 
	prenomPersonne, personne, role, n, mail, Formation) de la variable $session, 
	ces vérifications ont toutes une complexité constante et ne dépendent pas de la taille des entrées.
	*/	
		
		return isset($session["nomPersonne"]) && isset($session["prenomPersonne"]) && isset($session["personne"])
		&& isset($session["role"]) && isset($session["n"]) && isset($session["mail"])
		&& isset($session["Formation"]);
	}
	
function userValide($session,$user){
	/* 
	Cette fonction vérifie que le tableau de la session 
		et le tableau renvoyer par userExist du Model
		envoyé en paramètre contiennent les mêmes informations.
	La complexité de cette fonction est en O(1), car elle effectue un certain nombre de comparaisons (vérification de l'égalité des valeurs des champs nomPersonne, 
	prenomPersonne, personne, role, mail, Formation) 
	entre les données stockées dans les variables $session et $user, ces comparaisons ont toutes une complexité constante et ne dépendent pas de la taille des entrées. 
	*/
	return $session["nomPersonne"]==$user["nomPersonne"] && $session["prenomPersonne"]==$user["prenomPersonne"] && $session["personne"]==$user["personne"]
		&& $session["role"]==$user["role"] && $session["n"]==$user["n"] && $session["mail"]==$user["mail"]
		&& $session["Formation"]==$user["Formation"];
	
}
		
		
		


function typeValide($type){
	/*
	Cette fonction vérifie si le type entrer en paramètre est valide pour nos besoins.
	La complexité de cette fonction est en O(n), car elle utilise la fonction "in_array" qui parcours tous les éléments du tableau $types pour voir si $type y est présent. 
	Le temps d'exécution de la fonction augmente proportionnellement avec la taille de $types, si le tableau est plus grand, le parcours sera plus long.
	*/
	$types=["BOS","CV","LM","JDB","RS","RSF"];
	return in_array($type,$types);
	
}

function typePhrase($type){
	/*
	Cette fonction est utilisé pour la page d'upload (de dépots de fichiers/documents).
	Elle renvoie une phrase pour compléter le "Ajouté " dans la page d'upload, avec le type reçu en paramètre.
		Si le type n'est pas valide, elle renvoie false.
	La complexité de cette fonction est en O(1) , car elle fonctionne de la même manière que les fonctions précédentes. 
	Elle utilise la fonction "typeValide()" pour vérifier si la variable $type est égale à une des valeurs possibles ("BOS", "CV", "LM", "JDB", "RS", "RSF"), 
	et si c'est le cas, elle renvoie une chaine de caractère correspondante. Sinon, elle renvoie false.
	*/
	if (typeValide($type)){
		if ($type=="BOS"){
			return "un bordereau d'offre de stage";
		}
		
		elseif ($type=="CV"){
			return "un CV";
		}
		
		elseif ($type=="LM"){
			return "une lettre de motivation";
		}
		
		elseif ($type=="JDB"){
			return "un journal de bord";
		}
		
		elseif ($type=="RS"){
			return "un mini-rapport de stage";
		}
		
		elseif ($type=="RSF"){
			return "le rapport final de stage";
		}
	}
	return false;
	
}

function typeDeDocument($type){
	/*
	Cette fonction est utilisé pour la base de données.
	Elle renvoie un le type qui va être montrer dans la base de données, avec le type reçu en paramètre.
		Si le type n'est pas valide, elle renvoie false.
		Ex : si $type='BOS' elle retourne "Bordereau_d-offre_de_Stage".
	La complexité de cette fonction est en O(1).
	Elle vérifie si la variable $type est égale à une des valeurs possibles ("BOS", "CV", "LM", "JDB", "RS", "RSF"), 
	et si c'est le cas, elle renvoie une valeur correspondante. Sinon, elle renvoie false.
	*/
	if (typeValide($type)){
		if ($type=="BOS"){
			return "Bordereau_d-offre_de_Stage";
		}
		
		elseif ($type=="CV"){
			return "CV";
		}
		
		elseif ($type=="LM"){
			return "Lettre_de_Motivation";
		}
		
		elseif ($type=="JDB"){
			return "Journal_De_Bord";
		}
		
		elseif ($type=="RS"){
			return "Mini_Rapport_de_Stage";
		}
		
		elseif ($type=="RSF"){
			return "Rapport_final";
		}
	}
	return false;
	
}

function typeDoc($type){
	/*
	Cette fonction est utilisé pour récupérer le type de la base de données et l'afficher d'une manière plus adapté a l'utilisateur sur la page enseignant et étudiant.
	Elle renvoie un le type qui va être montrer sur les pages dit précédement, avec le type reçu en paramètre.
		Si le type n'est pas valide, elle renvoie false.
		Ex : si $type='Lettre_de_Motivation' elle retourne "Lettre de Motivation".
	La complexité de cette fonction est en O(1), car elle ne dépend pas de la taille des entrées, 
	elle vérifie simplement si la variable $type est égale à une des valeurs possibles 
	( "Bordereau_d-offre_de_Stage", "CV", "Lettre_de_Motivation", 
	"Journal_De_Bord", "Mini_Rapport_de_Stage", "Rapport_final") , 
	et si c'est le cas, elle renvoie une valeur correspondante. Sinon, elle renvoie false. 
	*/
		if ($type=="Bordereau_d-offre_de_Stage"){
			return "BOS";
		}
		
		elseif ($type=="CV"){
			return "CV";
		}
		
		elseif ($type=="Lettre_de_Motivation"){
			return "Lettre de Motivation";
		}
		
		elseif ($type=="Journal_De_Bord"){
			return "Journal De Bord";
		}
		
		elseif ($type=="Mini_Rapport_de_Stage"){
			return "Mini Rapport de Stage";
		}
		
		elseif ($type=="Rapport_final"){
			return "Rapport final de Stage";
		}
	
	return false;
	
}

function promos(){
	/*
    Cette fonction renvoie des promos selon l'année.
	Elle est utilisée dans l'inscription et sert a proposer des année de choix lors de la création de groupes.
		Ex : si on est en avant juillet, pour cette année elle donne ['2022-2023','2023-2024'],
			si on est après elle renverrai ['2023-2024','2024-2025'].
    La complexité de cette fonction est en O(1), car elle effectue un certain nombre d'opérations (calcul de la date actuelle, 
	décomposition de la date en mois et année, calcul des années pour les promotions) qui ont toutes une complexité constante 
	et ne dépendent pas de la taille des entrées. 
  */
	$dt = time();
	$date=date( "m-Y", $dt );
	$data=explode('-',$date);
	$annee=$data[1];
	if ($data[0]>7){
		$annee1=$annee;
		$annee2=$annee+1;
		$annee3=$annee+2;
		$promo1=[$annee1,$annee2];
		$promo2=[$annee2,$annee3];
		$promos=[implode('-',$promo1),implode('-',$promo2)];
	}
	else{
		$annee1=$annee-1;
		$annee2=$annee;
		$annee3=$annee+1;
		$promo1=[$annee1,$annee2];
		$promo2=[$annee2,$annee3];
		$promos=[implode('-',$promo1),implode('-',$promo2)];
	}
	
	return $promos;
	
}
}
	