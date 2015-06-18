<?php
session_start();
header('Content-Type: application/json');
include_once('class.misc.php');
$misc = new misc();

if(isset($_POST['action'])){
	if($_POST['action'] == 'login'){
		$logged = $misc->checkLogin($_POST['id'], $_POST['passwd']);
		if($logged['status'] == 1){
			$result['success'] = true;
			$result['msg'] = "Welcome";
			$_SESSION[$logged['type']] = $logged['uid'];
		}
		elseif($logged['status'] == 0){
			$result['success'] = false;
			$result['msg'] = "User Id and Password do not Match";
		}
		elseif($logged['status'] == -1){
			$result['success'] = false;
			$result['msg'] = "User Id not Registered";
		}
		echo json_encode($result);
	}
	
	elseif($_POST['action'] == 'searchbook'){
		header('Content-Type: text/html');
		$prog = isset($_SESSION['student'])?$misc->getProg($_SESSION['student']):'';
		$books = $misc->searchBooks($prog,$_POST['key'],array('hits','book_no'),$_SESSION['student']);
		while($book = mysqli_fetch_assoc($books)) { ?>
			<li title="Click to Add" id="<?php echo $book['book_no']; ?>"><?php echo $book['title']; ?><br/>
			By: <?php echo $book['author']; ?> &nbsp;&nbsp;&nbsp;
			Copies: <?php echo $book['total_copies']; ?></li>
<?php	}
		if(mysqli_num_rows($books) == 0)
			echo "Nothing Found";
	}
	
	elseif($_POST['action'] == 'addchoice'){
		header('Content-Type: text/html');
		if(!isset($_SESSION['student']))
			echo 'Please Login to Fill Choices';
		else{
			$regno = $_SESSION['student'];
			$misc->addChoice($regno,$_POST['bookid']);
		}
	}
	
	elseif($_POST['action'] == 'updatechoice'){
		header('Content-Type: text/html');
		if(!isset($_SESSION['student']))
			echo 'Please Login to Fill Choices';
		else{
			$regno = $_SESSION['student'];
			unset($_POST['action']);
			$misc->updateChoice($regno,$_POST);
		}
	}
	
	elseif($_POST['action'] == 'movechoice'){
		header('Content-Type: text/html');
		if(!isset($_SESSION['student']))
			echo 'Please Login to Fill Choices';
		else{
			$regno = $_SESSION['student'];
			$misc->moveChoice($regno,$_POST['oldchoiceno'],$_POST['newchoiceno']);
		}
	}
	
	elseif($_POST['action'] == 'removechoice'){
		header('Content-Type: text/html');
		if(!isset($_SESSION['student']))
			echo 'Please Login...';
		else{
			$regno = $_SESSION['student'];
			$misc->removeChoice($regno,$_POST['bookid']);
		}
	}

	elseif($_POST['action'] == 'getchoices'){
		header('Content-Type: text/html');
		$books = $misc->getChoices($_SESSION['student']);
		if(mysqli_num_rows($books) == 0)
			echo 'You have Not Filled any Choices Yet';
		while($book = mysqli_fetch_assoc($books)) { ?>
		<li id="<?php echo $book['book_no']; ?>"><?php echo $book['title']; ?><br/>
		By: <?php echo $book['author']; ?> &nbsp;&nbsp;&nbsp;
		Copies: <?php echo $book['total_copies']; ?>
		<img src="images/remove.png" title="Click to Remove Book" alt="Remove" id="removeimg" /></li>
<?php	}
	}
	
	elseif($_POST['action'] == 'confirmchoice'){
		header('Content-Type: text/html');
		if(!isset($_SESSION['student']))
			echo "Please Login";
		else{
			$books = $misc->getChoices($_SESSION['student']);
			if(mysqli_num_rows($books) == 0)
				echo 'You have Not Filled any Choices Yet';
			elseif($misc->choiceConfirmed($_SESSION['student']))
				echo "You have Already Confirmed your Choices";
			else
				$misc->confirmChoice($_SESSION['student']);
		}
	}

	elseif($_POST['action'] == 'getfee'){
		$regno = $_POST['regno'];
		$result['valid'] = false;
		if(!$misc->isStudent($regno))
			$result['msg'] = "Please Check Registration No.";
		elseif($misc->isRegStudent(substr($regno,3)))
			$result['msg'] = "Student Already Registered";
		else{
			$result['valid'] = true;
			$result = array_merge($result,$misc->getInfo($regno));
			$result['course'] = $misc->getCourse($regno);
			$result['branch'] = $misc->getBranch($regno);
			$result['fee'] = $misc->getFees($regno);
		}
		echo json_encode($result);
	}

	elseif($_POST['action'] == 'confirmfee'){
		$result['success'] = 0;
		if(!$misc->isStudent($_POST['regno']))
			$result['msg'] = "Please Check Registration No.";
		elseif($misc->isRegStudent(substr($_POST['regno'],3)))
			$result['msg'] = "Student Already Registered";
		else{	
			if($misc->register($_POST['regno'])){
				$result['success'] = 1;
				$result['msg'] = "Collect Fee from Student";
				$result['info'] = $misc->receipt($_POST['regno']);
			}
			else
				$result['msg'] = "Server Error";
		}
		echo json_encode($result);
	}

	elseif($_POST['action'] == 'rereceipt'){
		$regno = $_POST['regno'];
		$result['valid'] = false;
		if(!$misc->isStudent($regno))
			$result['msg'] = "Please Check Registration No.";
		elseif(!$misc->isRegStudent(substr($regno,3)))
			$result['msg'] = "Student Not Yet Registered";
		else{
			$result['valid'] = true;
			$result = array_merge($result,$misc->getInfo($regno));
			$result['course'] = $misc->getCourse($regno);
			$result['branch'] = $misc->getBranch($regno);
			$result['fee'] = $misc->getFees($regno);
		}
		echo json_encode($result);
	}

	elseif($_POST['action'] == 'reprint'){
		$result['success'] = 0;
		if(!$misc->isStudent($_POST['regno']))
			$result['msg'] = "Please Check Registration No.";
		elseif(!$misc->isRegStudent(substr($_POST['regno'],3)))
			$result['msg'] = "Student Not Yet Registered";
		else{
			$result['info'] = $misc->receipt($_POST['regno']);
			$result['msg'] = "Receipt printed";
			$result['success'] = 1;
		}
		echo json_encode($result);
	}

	elseif($_POST['action'] == 'getcancelfee'){
		$result['valid'] = false;
		if(!$misc->isRegStudent($_POST['loginid']))
			$result['msg'] = "Student Not Yet Registered";
		else{
			$result['valid'] = true;
			$regno = $misc->getRegno($_POST['loginid']);
			$info = $misc->getInfo($regno);
			$result = array_merge($result,$misc->getInfo($regno));
			$result['course'] = $misc->getCourse($regno);
			$result['branch'] = $misc->getBranch($regno);
			$result['fee'] = $misc->getFees($regno);
		}
		echo json_encode($result);
	}

	elseif($_POST['action'] == 'confirmcancelfee'){
		$result['success'] = 0;
		if(!$misc->isRegStudent($_POST['loginid']))
			$result['msg'] = "Student Not Yet Registered";
		else{	
			if($misc->cancel($_POST['loginid'])){
				$result['success'] = 1;
				$result['msg'] = "Return Fee to Student";
			}
			else
				$result['msg'] = "Server Error";
		}
		echo json_encode($result);
	}

	elseif($_POST['action'] == 'bookedit'){
		$result['success'] = $misc->bookedit($_POST['bookno'],$_POST['title'],$_POST['author'],$_POST['cat'],$_POST['copies']);
		echo json_encode($result);
	}
	
	elseif($_POST['action'] == 'bookadd'){
		$result['bookno'] = $misc->bookadd($_POST['bookno'],$_POST['title'],$_POST['author'],$_POST['copies'],$_POST['cat']);
		echo json_encode($result);
	}
	
	elseif($_POST['action'] == 'booklist'){
		header('Content-Type: text/html');
		$misc->booklist($_POST['prog']);
	}
	
	elseif($_POST['action'] == 'tokenlist'){
		header('Content-Type: text/html');
		$sem = rtrim($_POST['sem'],',');
		$sems = explode(',',$sem);
		$bra = rtrim($_POST['bra'],',');
		$bras = explode(',',$bra);
		$misc->printtokenlist($_POST['prog'],$sems,$bras);
	}
	
	elseif($_POST['action'] == 'tokens'){
		header('Content-Type: text/html');
		$sem = rtrim($_POST['sem'],',');
		$sems = explode(',',$sem);
		$bra = rtrim($_POST['bra'],',');
		$bras = explode(',',$bra);
		$misc->printtokens($_POST['prog'],$sems,$bras);
	}
	
	elseif($_POST['action'] == 'changePswd'){
		if(isset($_SESSION['student']))
			$check = $misc->checkLogin(substr($_SESSION['student'],3), $_POST['old']);
		elseif(isset($_SESSION['staff']))
			$check = $misc->checkLogin($_SESSION['staff'], $_POST['old']);
		elseif(isset($_SESSION['admin']))
			$check = $misc->checkLogin($_SESSION['admin'], $_POST['old']);
		else
			$msg = "Please Login";
		
		if($check['status'] == 1){
			if(isset($_SESSION['student'])){
				if($misc->changePswd($_SESSION['student'], $_POST['new']))
					$msg = "Password Changed";
				else
					$msg = "Server Error";
			}
			elseif(isset($_SESSION['staff'])){
				if($misc->changePswd($_SESSION['staff'], md5($_POST['new'])))
					$msg = "Password Changed";
				else
					$msg = "Server Error";
			}
			elseif(isset($_SESSION['admin'])){
				if($misc->changePswd($_SESSION['admin'], md5($_POST['new'])))
					$msg = "Password Changed";
				else
					$msg = "Server Error";
			}
		}
		else
			$msg = "Password Incorrect";
		$status['msg']=$msg;
		echo json_encode($status);
	}
}

?>