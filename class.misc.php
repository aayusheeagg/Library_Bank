<?php
class misc{
	protected $sql;
	public function __construct(){
		include_once('class.tlbConfig.php');
		$config = new tlbConfig();
		$base = $config->baseServer;
		require_once($_SERVER['DOCUMENT_ROOT'].$base.'class.sql.php');
		$this->sql = new sql();
	}
	
	
	/** ---------------------Getting Student Datas---------------------- */
	
	public function getInfo($uid, $type='student'){
		if($type == 'student')
			$this->sql->query = "SELECT * FROM student WHERE regno='$uid'";
		else
			$this->sql->query = "SELECT * FROM staff WHERE uid='$uid'";
		$result=$this->sql->process();
		$row = mysqli_fetch_assoc($result);
		return $row;
	}
	
	public function getProg($regno){
		return $this->sql->getData('prog','student','regno',$regno);
	}
	
	public function getCourse($regno)
	{
		$this->sql->query = "SELECT course FROM course_map,student WHERE student.regno='$regno'
							AND student.prog=course_map.code";
		$result = $this->sql->process();
		$row = mysqli_fetch_assoc($result);
		return $row['course'];
	}

	public function getBranch($regno)
	{
		$this->sql->query = "SELECT branch FROM branch_map,student WHERE student.regno='$regno' AND student.bra=branch_map.code";
		$result = $this->sql->process();
		$row = mysqli_fetch_assoc($result);
		return $row['branch'];
	}
	
	public function getRegno($loginid){
		$regno = $this->sql->getData('uid','login','loginid',$loginid);
		return $regno;
	}
	
	public function getSemester(){
		return $this->sql->getData('semester','Session');
	}
	
	public function getFees($regno){
		return "Rs.".$this->sql->getData('amount','fee','prog',$this->sql->getData('prog','student','regno',$regno));
	}
		
	public function choiceConfirmed($regno){
		$this->sql->query = "SELECT book_no FROM choice WHERE regno='$regno' AND submit='y'";
		$result = $this->sql->process();
		return (mysqli_num_rows($result) > 0)? true : false;
	}
	
	public function booksCount($regno){
		$this->sql->query = "SELECT book_no FROM choice WHERE regno='$regno' AND alloted='y'";
		$result = $this->sql->process();
		return mysqli_num_rows($result);
	}
		
	public function maxCount($prog, $sem){
		$this->sql->query = "SELECT number FROM max_books WHERE type='$prog' AND sem=$sem";
		$result = $this->sql->process();
		$row = mysqli_fetch_assoc($result);
		return $row['number'];
	}
		
	/** ----------------------------Common Functions------------------------- */
	
	public function getCourseName($code)
	{
		return $this->sql->getData('course','course_map','code',$code);
	}

	public function getBranchName($code)
	{
		return $this->sql->getData('branch','branch_map','code',$code);
	}
	
	public function getSems($prog)
	{
		if($this->getSemester() == "Even")
			$this->sql->query = "SELECT sem FROM sem_order WHERE (sem%2=0) AND prog='$prog' ORDER BY pref";
		else
			$this->sql->query = "SELECT sem FROM sem_order WHERE (sem%2=1) AND prog='$prog' ORDER BY pref";
		return $this->sql->process();
	}

	public function getBras($prog)
	{
		$this->sql->query = "SELECT bra, branch FROM bra_order, branch_map WHERE code=bra AND prog='$prog' ORDER BY pref";
		return $this->sql->process();
	}

	public function changePswd($uid, $passwd){
		$this->sql->query = "UPDATE login SET password = '$passwd' WHERE uid = '$uid'";
		return $this->sql->process();
	}
	
	public function searchBooks($prog='bt', $input=null, $sortby = array('cat','book_no'),$regno='')
	{
		$query = "SELECT book_no, title, author, total_copies, rem_copies, cat FROM books_master WHERE ";
		if(!isset($_SESSION['admin']) and !isset($_SESSION['staff']) )
			$query = $query."total_copies>0 AND ";
		$query = $query."type LIKE '%$prog%'";
		$query = $query." AND book_no NOT IN (SELECT book_no FROM choice WHERE regno='$regno')";
		$query = $query." AND (";
		$keys = explode(" ",$input);
		foreach($keys as $key){
			$query = $query."title LIKE '%$key%' OR author LIKE '%$key%' OR ";
		}
		$query = rtrim($query,'OR ');
		$query = $query.") ORDER BY ";
		foreach($sortby as $order)
			$query = $query."$order, ";
		$query = rtrim($query,', ');
		$this->sql->query = $query;
		$array = $this->sql->process();
		return $array;
	}
	
	public function addChoice($regno,$bid){
		if($this->choiceConfirmed($regno))
			echo "You have confirmed your choices. No more changes can be made";
		else{
		$choiceno = $this->sql->countData('choice_no','choice','regno',$regno);
		$this->sql->query = "SELECT choice_no FROM choice WHERE regno='$regno' AND book_no='$bid'";
		$hasalready = mysqli_num_rows($this->sql->process());
		if($choiceno >= 10)
			echo "You have already added 10 books";
		elseif($hasalready >= 1)
			echo "You have already added this book";
		else{
			$choiceno++;
			$this->sql->query = "INSERT INTO choice VALUES('$regno',$choiceno,$bid,'n','n')";
			if($this->sql->process())
				echo 'Book Added';
			else
				echo 'Server Error!. Please try again';
		}
		}
	}
	
	public function updateChoice($regno,$choices){
		if($this->choiceConfirmed($regno))
			echo "You have confirmed your choices. No more changes can be made";
		else{
			$this->sql->query = "DELETE FROM choice WHERE regno='$regno'";
			$this->sql->process();
			$i = 1;
			foreach($choices as $choice){
				$this->sql->query = "INSERT INTO choice VALUES('$regno',$i,$choice,'n','n')";
				$this->sql->process();
				$i++;
			}
			echo "Choices Rearranged";
		}
	}
	
	public function moveChoice($regno,$choiceno,$new_choiceno){
		if($this->choiceConfirmed($regno))
			echo "You have confirmed your choices. No more changes can be made";
		else{
			$this->sql->query = "SELECT book_no FROM choice WHERE regno='$regno' AND choice_no='$choiceno'";
			$result = $this->sql->process();
			$row = mysqli_fetch_assoc($result);
			$bid = $row['book_no'];
			$this->sql->query = "DELETE FROM choice WHERE choice_no=$choiceno AND regno='$regno'";
			$this->sql->process();
			if($choiceno > $new_choiceno)
				$this->sql->query = "UPDATE choice SET choice_no=choice_no+1 WHERE regno = '$regno' AND choice_no < $choiceno AND choice_no >= $new_choiceno";
			else
				$this->sql->query = "UPDATE choice SET choice_no=choice_no-1 WHERE regno = '$regno' AND choice_no > $choiceno AND choice_no <= $new_choiceno";
			$this->sql->process();
			$this->sql->query = "INSERT INTO choice VALUES('$regno',$new_choiceno,$bid,'n','n')";
			$this->sql->process();
			echo "Choices Rearranged";
		}
	}
	
	public function removeChoice($regno,$bid){
		if($this->choiceConfirmed($regno))
			echo "You have confirmed your choices. No more changes can be made";
		else{
		$this->sql->query = "SELECT choice_no FROM choice WHERE regno='$regno' AND book_no='$bid'";
		$result = $this->sql->process();
		$hasalready = mysqli_num_rows($result);
		if($hasalready == 0)
			echo "You don't have this book in your Choices";
		else{
			$row = mysqli_fetch_assoc($result);
			$choiceno = $row['choice_no'];
			$this->sql->query = "DELETE FROM choice WHERE choice_no=$choiceno AND regno='$regno'";
			if($this->sql->process()){
				$this->sql->query = "UPDATE choice SET choice_no=choice_no-1 WHERE regno = '$regno' AND choice_no>$choiceno";
				$this->sql->process();
				echo 'Book Removed';
			}
			else
				echo 'Server Error!. Please try again';
		}
		}
	}

	public function confirmChoice($regno){
		$this->sql->query = "UPDATE choice SET submit='y' WHERE regno = '$regno'";
		if($this->sql->process())
			echo "Choices Confirmed. You cannot make any further changes";
		else
			echo 'Server Error!. Please try again';
	}

	public function getChoices($regno){
		$prog = $this->getProg($regno);
		$this->sql->query = "SELECT b.choice_no, b.alloted, a.title, a.author, a.total_copies, a.book_no FROM books_master a, choice b WHERE a.type = '$prog' AND b.regno = '$regno' AND a.book_no=b.book_no ORDER BY b.choice_no";
		$array = $this->sql->process();
		return $array;
	}
	
	/** ----------------------------Getting Server Infos------------------------- */
	
	public function getYear(){
		$year = $this->sql->getData('year','Session');
		return '20'.$year;
	}
	
	public function getSession(){
		$year = $this->sql->getData('year','Session');
		return '20'.$year.'-'.($year+1);
	}
	
	public function checkLogin($id, $passwd){
		$id = $this->sql->escape($id);
		$passwd = $this->sql->escape($passwd);
		if(!$this->isUser($id))
			$login['status'] = -1;
		else{
			if(!$this->isRegStudent($id))
				$passwd = md5($passwd);
			else
				$login['type'] = 'student';
			$this->sql->query = "SELECT uid,type FROM login WHERE loginid = '$id' AND password = '$passwd'";
			$result = $this->sql->process();
			if($login['status']=mysqli_num_rows($result))
				$login = array_merge($login, mysqli_fetch_assoc($result));
		}
		return $login;
	}
	
	public function isUser($uid){
		return $this->sql->countData('loginid','login','loginid',$uid);
	}

	public function isStudent($regno){
		if($this->sql->countData('regno','student','regno',$regno) == 1)
			return 1;
		else
			return 0;
	}

	public function isRegStudent($loginid){
		if($this->sql->getData('type','login','loginid',$loginid) == 'student')
			return true;
		else
			return false;
	}
	
	public function isLoggedIn(){
		if(isset($_SESSION['student']) or isset($_SESSION['admin']) or isset($_SESSION['staff']))
			return true;
		else
			return false;
	}
	
	public function loggedUser(){
		if(isset($_SESSION['student']))
			return 'student';
		elseif(isset($_SESSION['admin']))
			return 'admin';
		elseif(isset($_SESSION['staff']))
			return 'staff';
		else
			return false;
	}
	
	public function allotDone(){
		return false;
	}
	

	/** ---------------------------Staff Duties---------------------------- */
	
	
	public function register($uid, $passwd=''){
		if(isset($_SESSION['staff'])){
			$pass=mt_rand();
			$pass=substr($pass,0,6);
			
			$date=date('d-m-Y');
			$time=date('H:i:s');
			
			$officer = $_SESSION['staff'];
			
			$counter = $this->sql->getData("regd","counters");
			$counter++;
			$rcpt = "TLB/".$this->getYear()."/".$this->getSemester()."/".$counter;
			$fee = $this->sql->getData("amount","fee","prog",$this->sql->getData('prog','student','regno',$uid));
			
			$loginid = substr($uid,3);	
			$this->sql->query = "INSERT INTO login VALUES('$loginid','$pass','$uid','student')";
			$result1 = $this->sql->process();
			
			$progm=$this->sql->getData('prog','student','regno',$uid);
			$this->sql->query="INSERT INTO receipt VALUES('$rcpt','$uid','$date','$time','$officer','$fee','$progm')";
			$result2 = $this->sql->process();
				
			$this->sql->query="UPDATE counters SET regd = '$counter' WHERE 1";
			$result3 = $this->sql->process();
			
			if($result1 and $result2 and $result3)
				return 1;
			else
				return 0;
		}
		else
			return -1;
	}

	public function cancel($loginid){
		if(isset($_SESSION['staff'])){
			$regno = $this->getRegno($loginid);
			$data = $this->sql->getDatas(array('rec_no','collected_by'),'receipt','regno',$regno);
			$rec_no=$data['rec_no'];
		
			$time=date('H:i:s');
			$officer = $_SESSION['staff'];
			$coll_by = $data['collected_by'];
			$this->sql->query="INSERT INTO cancelled VALUES('$regno','$rec_no',now(),'$time','$officer','$coll_by')";
			$result1 = $this->sql->process();
					
			$this->sql->query="DELETE FROM login WHERE loginid='$loginid'";
			$result2 = $this->sql->process();
			if($result1 and $result2)
				return 1;
			else
				return 0;
		}
		else
			return -1;
	}
	
	public function receipt($regno){
		$info['regno'] = $regno;
		$info['name'] = $this->sql->getData('name','student','regno',$regno);
		$info['branch'] = $this->getBranch($regno);
		$info['course'] = $this->getCourse($regno);
/*		$info = array_merge($info,$this->sql->getDatas(array('rec_no','date','time','collected_by','amount'),'receipt',
						'regno',$regno));
		$info = array_merge($info,$this->sql->getDatas(array('loginid','password'),'login','uid',$regno));
*/
		$info['rec_no'] = $this->sql->getData('rec_no','receipt','regno',$regno);
		$info['date'] =$this->sql->getData('date','receipt','regno',$regno);
		$info['time'] =$this->sql->getData('time','receipt','regno',$regno);
		$info['collected_by'] =$this->sql->getData('collected_by','receipt','regno',$regno);
		$info['amount'] =$this->sql->getData('amount','receipt','regno',$regno);
		$info['loginid'] =$this->sql->getData('loginid','login','uid',$regno);
		$info['password'] =$this->sql->getData('password','login','uid',$regno);
		return $info;
	}
	
	public function getStats($uid){
		if(isset($_SESSION['staff']) or isset($_SESSION['admin'])){
			$stats['issues'] = $this->sql->countData("regno","receipt","collected_by",$uid);
			$stats['cancels'] = $this->sql->countData("regno","cancelled","cancelled_by",$uid);
			$stats['btcount'] = $this->sql->countStudents("regno","receipt","collected_by",$uid,"bt");
			$stats['mccount'] = $this->sql->countStudents("regno","receipt","collected_by",$uid,"mc");
			$stats['balance'] = ($stats['issues'] - $stats['cancels']) * 15;
			return $stats;
		}
		else
			return false;
	}
	
	public function getStats1($prog){
		if(isset($_SESSION['staff']) or isset($_SESSION['admin'])){
			$query = "SELECT uid FROM login WHERE uid ";
			if($prog == 'bt')
				$query = $query."NOT ";
			$this->sql->query = $query."LIKE '%ca%' AND type='student'";
			$stats['regn'] = mysqli_num_rows($this->sql->process());

			$stats['balance'] = $stats['regn'] * 15;
			return $stats;
		}
		else
			return false;
	}
	
	/** ---------------------------Admin Only Duties--------------------------- */
	
	public function bookedit($bookno, $title, $author, $cat, $copies){
		$book = explode("-",$bookno);
		$this->sql->query = "UPDATE books_master SET title='$title', author='$author', cat='$cat', total_copies='$copies' WHERE book_no='$book[1]' AND type='$book[0]'";
		if($this->sql->process())
			return 1;
		else
			return 0;
	}
	
	public function bookadd($bookno, $title, $author, $copies, $cat){
		$book = explode("-",$bookno);
		$this->sql->query = "INSERT INTO books_master VALUES('$book[1]', '$book[0]', '$title', '$author', '$copies', '$copies', '$cat', 0)";
		if($this->sql->process())
			return $book[0]."-".($book[1]+1);
		else
			return 0;
	}
	
	public function booklist($prog){
		global $base; ?>
		<table width="800" border="1" cellpadding="0" align="center" style="font-size:24px">
		<tr>
			<td width="120" align="center" valign="middle">
			<img src="<?php echo $base; ?>images/logo.png" width="105" height="130" /></td>
			<td width="670" align="center">
				<strong>Motilal Nehru National Institute of Technology<br /> Allahabad - 211004<br />
				TextBook Lending Bank<br />
<?php			if($prog == 'bt')
					echo "B.Tech.";
				else
					echo "MCA"; ?>
				Book List :: <?php echo $this->getSemester(); ?> Semester <?php echo $this->getSession(); ?><br />
				</strong>
			</td>
		</tr>
		</table>
		<br /><br />
<?php	$books = $this->searchBooks($prog);
		$sno = 10; ?>
		<table width="800" border="1" align="center" cellspacing="0" cellpadding="2" >
		<tr><th>Sl.No.</th><th>Title</th><th>Author</th><th>Copies</th></tr>
<?php	while($book = mysqli_fetch_assoc($books)) {
			$sno++;
			 ?>
			<tr>
			<td><?php echo $sno-10; ?></td>
			<td><?php echo $book['title']; ?></td>
			<td><?php echo $book['author']; ?></td>
			<td><?php echo $book['total_copies']; ?></td>
			</tr>
<?php	} ?>
		</table>
<?php
	}
	
	public function allot(){
		$progs = array("bt","mc");
		foreach($progs as $prog){
			if($this->getSemester() == "EVEN")
				$this->sql->query = "SELECT sem FROM sem_order WHERE (sem%2=0) AND prog='$prog' ORDER BY pref";
			else
				$this->sql->query = "SELECT sem FROM sem_order WHERE (sem%2=1) AND prog='$prog' ORDER BY pref";
			$res1 = $this->sql->process();
			
			while($sems = mysqli_fetch_assoc($res1)){
				$sem = $sems['sem'];
				$finish = false;

				while(!$finish){
					$finish = true;
					if($sem == 1)
						$this->sql->query = "SELECT regno, cpi FROM student WHERE prog='$prog' AND sem=$sem AND
											regno IN (SELECT uid FROM login WHERE type='student') AND category!='DASA' and category!='MEA' and category!='ICCR' ORDER BY cpi ASC";
					else
						$this->sql->query = "SELECT regno, cpi FROM student WHERE prog='$prog' AND sem=$sem AND
											regno IN (SELECT uid FROM login WHERE type='student') ORDER BY cpi DESC";
							
					$res2 = $this->sql->process();
				
					while($student = mysqli_fetch_assoc($res2)){
						if($this->booksCount($student['regno']) < $this->maxCount($prog,$sem)){
							$this->sql->query = "SELECT * FROM choice WHERE regno = '$student[regno]' AND alloted='n' ORDER BY choice_no ASC";
							$res3 = $this->sql->process();
							
							while($choice = mysqli_fetch_assoc($res3)){
								$this->sql->query = "SELECT * FROM books_master WHERE type='$prog' AND book_no=$choice[book_no]";
								$res4 = $this->sql->process();
								$book = mysqli_fetch_assoc($res4);
								
								if($book['rem_copies'] > 0){
									$this->sql->query = "UPDATE choice SET alloted='y' where regno='$student[regno]' AND choice_no=$choice[choice_no]";
									$this->sql->process();
									$this->sql->query = "UPDATE books_master SET rem_copies=rem_copies-1 WHERE book_no=$choice[book_no] AND type='$prog'";
									$this->sql->process();
									
									$finish = false;
									break;
								}
							}
						}
					}
				}
			}
		}
	}
	
	public function generatetoken(){
		$token = $this->sql->getData('token','counters');
		$progs = array("bt","mc");
		foreach($progs as $prog){
			if($this->getSemester() == "EVEN")
				$this->sql->query = "SELECT sem FROM sem_order WHERE (sem%2=0) AND prog='$prog' ORDER BY pref";
			else
				$this->sql->query = "SELECT sem FROM sem_order WHERE (sem%2=1) AND prog='$prog' ORDER BY pref";
			$res1 = $this->sql->process();
			while($sems = mysqli_fetch_assoc($res1)){
				$sem = $sems['sem'];
				$this->sql->query = "SELECT bra, branch FROM bra_order, branch_map WHERE code=bra ORDER BY pref";
				$res2 = $this->sql->process();
				while($bras = mysqli_fetch_assoc($res2)){
					$bra = $bras['bra'];
					if($sem == 1)
						$this->sql->query = "SELECT regno FROM student WHERE regno IN (SELECT regno FROM choice) AND prog='$prog' AND sem=$sem AND bra='$bra' ORDER BY cpi ASC, regno ASC";
					else
						$this->sql->query = "SELECT regno FROM student WHERE regno IN (SELECT regno FROM choice) AND prog='$prog' AND sem=$sem AND bra='$bra' ORDER BY cpi DESC, regno ASC";
						
					$res3 = $this->sql->process();
					while($student = mysqli_fetch_assoc($res3)){
						if($this->booksCount($student['regno']) != 0 and $this->sql->countData('regno','tokens','regno',$student['regno']) == 0){
							$token++;
							$this->sql->query = "INSERT INTO tokens VALUES('$student[regno]', $token)";
							$this->sql->process();
							$this->sql->query = "UPDATE counters SET token=$token WHERE 1";
							$this->sql->process();
						}
					}
				}
			}
		}
	}
	
	public function printtokenlist($prog, $sems, $bras){
		global $base;
		$prefix = $this->getYear() . "/";
		if($prog == 'mc')
			$prefix = $prefix."MCA/";
		foreach($sems as $sem){
			foreach($bras as $bra){
				$this->sql->query = "SELECT student.regno AS regno, token, name FROM student, tokens WHERE prog='$prog' AND sem=$sem AND bra='$bra' AND tokens.regno=student.regno ORDER BY token ASC";
				$res = $this->sql->process();
				if(mysqli_num_rows($res) != 0){ ?>
					<table width="850" border="1" cellpadding="0" align="center" style="font-size:28px">
					<tr>
						<td width="126" align="center" valign="middle">
						<img src="<?php echo $base; ?>images/logo.png" width="105" height="130" /></td>
						<td width="718" align="center">
							<strong>Motilal Nehru National Institute of Technology<br /> Allahabad - 211004<br />
							Text Book Lending Bank Allotment Results <br />
							(<?php echo $this->getSemester(); ?> Semester <?php echo $this->getSession(); ?>)</strong>
						</td>
					</tr>
					</table>
					<br />
					<table width="700" border="0" align="center" style="text-align:left; font-weight:bold; font-size:26px;">
					<tr>
						<th width="127">Program:</th>
						<td width="657"><?php echo $this->getCourseName($prog); ?></td>
					</tr>
					<tr>
						<th colspan="1">Branch:</strong></th>
						<td colspan="3"><?php echo $this->getBranchName($bra); ?></td>
					</tr>
					<tr>
						<th height="31" colspan="1"><strong>Semester:</th>
						<td colspan="2"><?php echo $sem; ?></td>
					</tr>
					</table>
					<br />
					<table width="955" border="1" cellpadding="3" cellspacing="0" align="center" style="font-size:26px">
					<tr height="42">
						<th width="73">Sl No</th>
						<th width="219">Registration Number</th>
						<th width="345">Name</th>
						<th width="116">Token No</th>
						<th width="190">Number of Books</th>
					</tr>
					
<?php				
					$slno = 1;
					while($student = mysqli_fetch_assoc($res)){ ?>
						<tr height="40">
						<td align="center"><b><?php echo $slno++; ?></b></td>
						<td><b><?php echo $student['regno']; ?></b></td>
						<td><b><?php echo ucwords(strtolower($student['name'])); ?></b></td>
						<td align="center"><b><?php echo $prefix.$student['token']; ?></b></td>
						<td align="center"><b><?php echo $this->booksCount($student['regno']); ?></b></td>
						</tr>
<?php					} ?>
					</table>
					<div style="page-break-after:always"></div>
<?php				}
				else
					echo "No Student for Branch ".$this->getBranchName($bra).", Semester $sem"; ?>
					<div style="page-break-after:always"></div>
<?php
			}
		}
	}
	
	public function printtokens($prog, $sems, $bras){
		global $base;
		$prefix = $this->getYear() . "/";
		if($prog == 'mc')
			$prefix = $prefix."MCA/";
		foreach($sems as $sem){
			foreach($bras as $bra){
				$branch = $this->getBranchName($bra);
				$this->sql->query = "SELECT student.regno AS regno, token, name FROM student, tokens WHERE prog='$prog' AND sem=$sem AND bra='$bra' AND tokens.regno=student.regno ORDER BY token ASC";
				$res = $this->sql->process();
				if(mysqli_num_rows($res) != 0){
					while($student = mysqli_fetch_assoc($res)){ ?>
						<table width="950" border="1" cellpadding="0" align="center" style="font-size:28px">
						<tr>
							<td width="263" align="center" valign="middle">
							<img src="<?php echo $base; ?>images/logo.png" width="220" height="248" /></td>
							<td align="center">
								<strong>Motilal Nehru National Institute of Technology<br /> Allahabad - 211004<br />
								Text Book Lending Bank Allotment Results <br />
								(<?php echo $this->getSemester(); ?> Semester <?php echo $this->getSession(); ?>)</span></strong>
							</td>
						</tr>
						</table>
						<br />
						<table width="800" border="0" align="center" style="font-size:24px; text-align:left">
						<tr>
							<th width="250">Registration Number:</th>
							<td width="150"><?php echo $student['regno']; ?></td>
							<th width="200">Token Number:</th>
							<td width="200"><?php echo $prefix.$student['token']; ?></td>
						</tr>
						<tr><th>Name:</th><td colspan="3"><?php echo ucwords(strtolower($student['name'])); ?></td></tr>
						<tr><th>Program:</th><td colspan="3">
							<?php echo $this->getCourseName($prog); ?></td>
						</tr>
						<tr><th>Branch:</th><td colspan="3"><?php echo $branch; ?></td></tr>
						<tr>
							<th height="31" colspan="1"><strong>Semester:</th>
							<td colspan="2"><?php echo $sem; ?></td>
						</tr>
						</table>
						<br /> <br />
						<font size="+2"><center>The following is the final result of allotment of books for the Text Book Lending Bank
						</font></center>
						<br />
						<table width="955" border="1" cellpadding="3" cellspacing="0" align="center" style="font-size:26px; font-weight:bold">
						<tr>
							<th width="73">Sl No</th>
							<th width="400">Title</th>
							<th width="200">Author</th>
							<th width="80">Preference</th>
							<th width="80">Status</th>
						</tr>
<?php					$this->sql->query = "SELECT a.title, a.author, b.choice_no, b.alloted FROM books_master a, choice b WHERE a.type = '$prog' AND b.regno = '$student[regno]' AND a.book_no=b.book_no ORDER BY b.choice_no";
						$res2 = $this->sql->process();
						while($book = mysqli_fetch_assoc($res2)){ ?>
							<tr>
								<td align="center"><?php echo $book['choice_no']; ?></td>
								<td><?php echo $book['title']; ?></td>
								<td><?php echo $book['author']; ?></td>
								<td align='center'><?php echo $book['choice_no']; ?></td>
								<td align='center'><?php echo strtoupper($book['alloted']); ?></td>
							</tr>
<?php						} ?>
						</table>
						<br /> <br /> <br />
						<center>
							<b>FOR LIBRARY USE ONLY<br /> <br /> Received _____________________________ books for Session 2013-14<br />
							<br /> <br />Signature of I/C TLB ____________________ &nbsp;&nbsp;&nbsp;&nbsp;
							Signature of Borrower _______________________</strong>
						</center>
						<div style="page-break-after:always"></div>
<?php					}
				}
			}
		}
	}
	
	public function printtokens2(){
		global $base;
		$progs = array("mc");
		foreach($progs as $prog){
			$prefix = $this->getYear() . "/";
			if($prog == 'mc')
				$prefix = $prefix."MCA/";
			if($this->getSemester() == "Even")
				$this->sql->query = "SELECT sem FROM sem_order WHERE (sem%2=0) AND prog='$prog' ORDER BY pref";
			else
				$this->sql->query = "SELECT sem FROM sem_order WHERE (sem%2=1) AND prog='$prog' ORDER BY pref";
			$res1 = $this->sql->process();
			while($sems = mysqli_fetch_assoc($res1)){
				$sem = $sems['sem'];
				$this->sql->query = "SELECT bra, branch FROM bra_order, branch_map WHERE code=bra ORDER BY pref";
				$res2 = $this->sql->process();
				while($bras = mysqli_fetch_assoc($res2)){
					$this->sql->query = "SELECT student.regno AS regno, token, name FROM student, tokens WHERE prog='$prog' AND sem=$sems[sem] AND bra='$bras[bra]' AND tokens.regno=student.regno ORDER BY token ASC";
					$res3 = $this->sql->process();
					if(mysqli_num_rows($res3) != 0){
						while($student = mysqli_fetch_assoc($res3)){ ?>
							<table width="950" border="1" cellpadding="0" align="center" style="font-size:28px">
							<tr>
								<td width="263" align="center" valign="middle">
								<img src="<?php echo $base; ?>images/logo.png" width="220" height="248" /></td>
								<td align="center">
									<strong>Motilal Nehru National Institute of Technology<br /> Allahabad - 211004<br />
									Text Book Lending Bank Allotment Results <br />
									(<?php echo $this->getSemester(); ?> Semester <?php echo $this->getSession(); ?>)</span></strong>
								</td>
							</tr>
							</table>
							<br />
							<table width="800" border="0" align="center" style="font-size:24px; text-align:left">
							<tr>
								<th width="250">Registration Number:</th>
								<td width="150"><?php echo $student['regno']; ?></td>
								<th width="200">Token Number:</th>
								<td width="200"><?php echo $prefix.$student['token']; ?></td>
							</tr>
							<tr><th>Name:</th><td colspan="3"><?php echo ucwords(strtolower($student['name'])); ?></td></tr>
							<tr><th>Program:</th><td colspan="3">
								<?php echo $this->getCourseName($prog); ?></td>
							</tr>
							<tr><th>Branch:</th><td colspan="3"><?php echo $bras['branch']; ?></td></tr>
							<tr>
								<th height="31" colspan="1"><strong>Semester:</th>
								<td colspan="2"><?php echo $sems['sem']; ?></td>
							</tr>
							</table>
							<br /> <br />
							<font size="+2"><center>The following is the final result of allotment of books for the Text Book Lending Bank
							</font></center>
							
							<table width="955" border="1" cellpadding="3" cellspacing="0" align="center" style="font-size:26px; font-weight:bold">
							<tr>
								<th width="73">Sl No</th>
								<th width="400">Title</th>
								<th width="200">Author</th>
								<th width="80">Preference</th>
								<th width="80">Status</th>
							</tr>
	<?php					$this->sql->query = "SELECT a.title, a.author, b.choice_no, b.alloted FROM books_master a, choice b WHERE a.type = '$prog' AND b.regno = '$student[regno]' AND a.book_no=b.book_no ORDER BY b.choice_no";
							$slno = 1;
							$res4 = $this->sql->process();
							while($book = mysqli_fetch_assoc($res4)){ ?>
								<tr>
									<td align="center"><?php echo $slno++; ?></td>
									<td><?php echo $book['title']; ?></td>
									<td><?php echo $book['author']; ?></td>
									<td align='center'><?php echo $book['choice_no']; ?></td>
									<td align='center'><?php echo strtoupper($book['alloted']); ?></td>
								</tr>
<?php						} ?>
							</table><br /> <br />
							<center>
								<b>FOR LIBRARY USE ONLY<br /> <br /> Received _____________________________ books for Session 2013-14<br />
								Signature of I/C TLB ____________________ &nbsp;&nbsp;&nbsp;&nbsp;
								Signature of Borrower _______________________</strong>
							</center>
				
							<div style="page-break-after:always"></div>
<?php					}
					}
				}
			}
		}
	}
	
	
/*
	public function flushdb(){
		if(isset($_SESSION['admin'])){
			$queries = array("TRUNCATE TABLE cancelled","TRUNCATE TABLE choice","DELETE FROM login WHERE type='student'",
			"UPDATE counters SET regd=0,token=0 WHERE 1","TRUNCATE TABLE receipt","TRUNCATE TABLE tokens",
			"UPDATE books_master SET rem_copies = total_copies WHERE 1");
			foreach($queries as $query){
				$this->sql->query = $query;
				$this->sql->process();
			}
		}
	}
*/
	/** --------------------------Menus--------------------------- */
	
	public function add_navmenu(){
		global $base; ?>
		<ul id="nav-menu">
			<li><a href="<?php echo $base; ?>home"><?php if($this->isLoggedIn()){ ?>Home<?php } else { ?>Login<?php } ?></a></li>
			<li><a href="<?php echo $base; ?>instruction">Instructions</a></li>
<?php	if(!isset($_SESSION['admin']) and !isset($_SESSION['staff'])) {?>
			<li><a href="<?php echo $base; ?>books">Books</a></li>
			<li><a href="<?php echo $base; ?>notice">Notice</a></li>
            
<?php	}
		elseif(isset($_SESSION['staff'])) { ?>
			<li><a href="<?php echo $base; ?>feecollect">Fees</a></li>
			<li><a href="<?php echo $base; ?>stats">Stats</a></li>
			<li><a href="<?php echo $base; ?>bookedit">Edit Books</a></li>            
<?php	}
		else { ?>
			<li><a href="<?php echo $base; ?>bookedit">Edit Books</a></li>
			<li><a href="<?php echo $base; ?>allot">Allot</a></li>
            <li><a href="<?php echo $base; ?>print">Print</a></li>
			
<?php	} ?>
			<li><a href="<?php echo $base; ?>contact">Contact Us</a></li>
			<?php if($this->isLoggedIn()){?>
			<li><a href="<?php echo $base; ?>logout">Logout</a></li>
			<?php } ?>
		</ul>
<?php
	}
	
	public function add_sidemenu($page){
		global $base; ?>
		<ul id="side_menu">
		<li><a href="">Quick Links</a>
			<ul style="display:block">
			<li><a href="<?php echo $base; ?>home"><?php if($this->isLoggedIn()){ ?>Home<?php } else { ?>Login<?php } ?></a></li>

			<?php if($page == "feecollect" or $page == "reprint" or $page == "feecancel") {?>
			<li><a href="<?php echo $base; ?>feecollect">Collect Fees</a></li>
			<li><a href="<?php echo $base; ?>reprint">Reprint Receipt</a></li>
			<li><a href="<?php echo $base; ?>feecancel">Cancel Fees</a></li><?php } ?>

			<?php if($page == "print") {?>
			<li><a href="<?php echo $base; ?>feecancel">Cancel Fees</a></li><?php } ?>
			<li><a href="<?php echo $base; ?>password"><?php if($this->isLoggedIn()){ ?>Change Password
						<?php } else { ?>Forgot Password<?php } ?></a></li>

			<?php if($this->isLoggedIn()){ ?><li><a href="<?php echo $base; ?>logout">Logout</a></li><?php } ?>
			</ul>
		</li>
		</ul>
<?php
	}


} ?>