<?php
	include('../config/init.php');
	include('../database/users.php');
	include('../database/requests.php');
	include('../database/chat.php');
	include('../tools/request.php');

	$title = strip_tags($_POST['title']);
	$location = strip_tags($_POST['location']);
	$date = date('Y-m-d', strtotime($_POST['date']));
	$reward = strip_tags($_POST['reward']);
	$description = strip_tags($_POST['description']);
	$_SESSION['form_values'] = $_POST;

  $_SESSION['current_request'] = $_CURR_REQ;
  $request = getRequest($_CURR_REQ);

	if(!$title) {
		$title = $request['name'];
	} elseif(!$location) {
		$location = $request['location'];
	} elseif(!$date) {
		$date = $request['date_limit'];
	}
	if(isset($_POST['skills'])){
		$skills = $_POST['skills']; /** Array com ids de skills selecionadas **/
	} else $skills = NULL;

	try{
		if(updateRequest($request['id'], $title, $location, $date, $reward, $description, $skills) == false) {
			$_SESSION['error_message'] = 'Problem updating request';
			die(header('Location: ../edit_request.php'));
		}
	}
	catch(PDOException $e) {
    $_SESSION['error_message'] = $e->getMessage();
    die(header('Location: ../edit_request.php'));
	}



	if(!isset($_FILES['fileToUpload']) || $_FILES['fileToUpload']['error'] == UPLOAD_ERR_NO_FILE) {
    /** DO NOTHING **/
  	} else {
		$extension = explode('.' , basename($_FILES["fileToUpload"]["name"]));
		$extension = '.' . end($extension);

		$target_dir = "../res/uploads/requests/";
		$target_file = $target_dir .$request['id'] . $extension;
		$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
		// Check if image file is a actual image or fake image
		if(isset($_POST["submit"])) {
      // $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
      $check = exif_imagetype($_FILES["fileToUpload"]["tmp_name"]);
      // if(!($check !== false)) {
      if($check != IMAGETYPE_GIF && $check != IMAGETYPE_JPEG && $check != IMAGETYPE_PNG) {
        $_SESSION['error_message'] = "O ficheiro tem de ser uma imagem JPG, JPEG, PNG ou GIF.";
        die(header('Location: ../edit_request.php'));
      }
		}

		// Check if file already exists
		$possibleImagePath = getRequestPhoto2($request['id']);

	
		if (file_exists($possibleImagePath)) {
	      $status = unlink($possibleImagePath); //remove the file
	      if($status != true){
	        //$_SESSION['error_message'] = $possibleImagePath . '-> não consegue apagar isto';
	        $_SESSION['error_message'] = 'Ocorreu um problema ao eliminar a imagem atual.';
          die(header('Location: ../edit_request.php'));
	      }
	    }

		// Check file size
		if ($_FILES["fileToUpload"]["size"] > 2000000) {
			//echo "Sorry, your file is too large.";
			$_SESSION['error_message'] = "A imagem não pode exceder 2MB";
      die(header('Location: ../edit_request.php'));
		}

    // Allow certain file formats - necessário? já foi verificado atrás se é imagem, podemos permitir mais tipos
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
    && $imageFileType != "gif" ) {
 		 //echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
  		$_SESSION['error_message'] = "A imagem tem de ser JPG, JPEG, PNG ou GIF.";
      die(header('Location: ../edit_request.php'));
		}



		// if everything is ok, try to upload file
		if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
		//echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
		$_SESSION['success_message'] = 'Pedido alterado com sucesso.';
		} else {
			$_SESSION['error_message'] = 'Houve um problema com o carregamento da imagem.';
      die(header('Location: ../edit_request.php'));
		}
	}

	header("Location: ../request.php?id=" . $request['id']);
?>
