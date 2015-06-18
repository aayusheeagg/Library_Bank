<?php
class pagecontent{
	public function __construct(){
		include_once('class.tlbConfig.php');
		$config = new tlbConfig();
		$base = $config->baseServer;
		require_once($_SERVER['DOCUMENT_ROOT'].$base.'class.sql.php');
		$this->sql = new sql();
	}
	public function title($page){
		global $misc;
		if($page == 'instruction')
			return 'Instructions';
		elseif($page == 'books')
			return 'Books';
		elseif($page == 'choice')
			if(!isset($Student)) $page='home';
			else return "Fill Choices";
		elseif($page == 'notice')
			return 'Notice';
		elseif($page == 'contact')
			return 'Contact Us';
		elseif($page == 'password'){
			if($misc->isLoggedIn())
				return 'Change Password';
			else
				return 'Recover Password';
		}
		elseif($page == 'feecollect'){
			if(!isset($_SESSION['admin']) and !isset($_SESSION['staff']))
				$page = "home";
			else
				return 'Fee Collection';
		}
		elseif($page == 'reprint'){
			if(!isset($_SESSION['admin']) and !isset($_SESSION['staff']))
				$page = "home";
			else
				return 'Re-print Receipt';
		}
		elseif($page == 'print'){
			if(!isset($_SESSION['admin']))
				$page = "home";
			else
				return 'Print details';
		}
		elseif($page == 'tokenlist'){
			if(!isset($_SESSION['admin']))
				$page = "home";
			else
				return 'Print token list';
		}
		elseif($page == 'tokens'){
			if(!isset($_SESSION['admin']))
				$page = "home";
			else
				return 'Print individual tokens';
		}
		
		elseif($page == 'feecancel'){
			if(!isset($_SESSION['admin']) and !isset($_SESSION['staff']))
				$page = "home";
			else
				return 'Cancel Fee';
		}
		elseif($page == 'issue'){
			if(!isset($_SESSION['admin']) and !isset($_SESSION['staff']))
				$page = "home";
			else
				return 'Issue Books';
		}
		elseif($page == 'return'){
			if(!isset($_SESSION['admin']) and !isset($_SESSION['staff']))
				$page = "home";
			else
				return 'Return Books';
		}
		elseif($page == 'stats'){
			if(!isset($_SESSION['admin']) and !isset($_SESSION['staff']))
				$page = "home";
			else
				return 'Stats';
		}
		elseif($page == 'bookedit'){
             if(!isset($_SESSION['admin']) and !isset($_SESSION['staff']))
				$page = "home";
			else
				return 'Edit books';
        }
		if($page == 'home'){
			if($misc->isLoggedIn())
				return 'Personal Information';
			else
				return 'Login';
		}
	}
	
	
	public function content($page){
		global $misc,$base,$Student,$Admin,$Staff;
		
		if($page == 'instruction'){ 
			if(!isset($Admin) and !isset($Staff)){ ?>
			<ul id="instructions">
			<li>Search for the book of your choice by either typing in the name of the book or the author.</li>
			<li>The result of any search contains all the valid matches with each word of your search key.</li>
			<li>Click to add or remove a book.</li>
			<li>Drag and drop to reorder.</li>
			<li>Note: You cannot add the same book more than once</li>
			<li>And can choose 10 books at the most.</li>
			<li>Every change that you make in your choices has a permanent effect and is reflected even on refreshing the page or after logging out and then again login in.</li>
			<li>You cannot make any further changes after confirming your choices.</li>
			</ul>
<?php		}
		else{ ?>
			<ul id="instructions">
			<li>This interface is meant only for the administrator and the library staff.</li>
			<li>The links provided above are to be used for the purpose of Fee Collection
			<?php if(isset($Admin)) echo ",Edit Details, Manage Admins and Staffs, Allot Books"; ?> and Issuing Books.</li>
			<li>The links are provided in exactly the same sequence as the jobs to be performed.</li>
			</ul>
<?php	}
		}
		elseif($page == 'books'){  ?>
			<input type="button" class="bookSwap" value="B.Tech." name="btech" />
			<input type="button" class="bookSwap" value="MCA" name="mca"/>
			<table id="books" class="btech" style="display:none">
<?php		$books = $misc->searchBooks('bt'); ?>
			<thead><tr><th width="400px">Title</th><th width="220px">Author</th><th width="80px">Copies</th></tr></thead>
			<tbody>
			<?php while($book = mysqli_fetch_assoc($books)) { ?>
			<tr><td width="400px"><?php echo $book['title']; ?></td><td width="220px"><?php echo $book['author']; ?></td>
				<td width="80px"><?php echo $book['total_copies']; ?></td></tr>
			<?php } ?></tbody>
			</table>
			<table id="books" class="mca" style="display:none">
<?php		$books = $misc->searchBooks('mc'); ?>
			<thead><tr><th width="400px">Title</th><th width="220px">Author</th><th width="80px">Copies</th></tr></thead>
			<tbody>
			<?php while($book = mysqli_fetch_assoc($books)) { ?>
			<tr><td width="400px"><?php echo $book['title']; ?></td><td width="220px"><?php echo $book['author']; ?></td>
				<td width="80px"><?php echo $book['total_copies']; ?></td></tr>
			<?php } ?></tbody>
			</table>
<?php	}
					
			elseif($page == 'choice'){ 
				if(!isset($Student))
					$page='home';
	
			elseif(1==1){
				echo "<center><h3>ALLOTED BOOKS</h3></center>";
				
				$books = $misc->getChoices($_SESSION['student']); ?>
				<table id="confirmedchoice" border=1 cellspacing="0">
				<tr align="center"><th>Sl. No</th><th>Title</th><th>Author</th><th>Alloted</th></tr>
<?php			while($book = mysqli_fetch_assoc($books)) { ?>
				<tr><td align="center"><?php echo $book['choice_no']; ?></td>
					<td><?php echo $book['title']; ?></td>
					<td><?php echo $book['author']; ?></td>
					<td align="center"><?php echo strtoupper($book['alloted']); ?></td>
				</tr>
<?php			} ?>
				</table>
				
<?php		}

			elseif(!$misc->choiceConfirmed($_SESSION['student'])){?>
				<div id="booksearch" class="unselectable">
				<input type="text" placeholder="Search By Title or Author" name="searchbook" />
				<ul id="searchlist">
				</ul>
				</div>
				<div id="bookchoices" class="unselectable">
				<strong>Your Choices</strong><br />
				<span id="msg"></span><br />
				<ul id="choicelist">
				</ul>
				<input type="button" class="confirmchoice" value="Confirm">
				</div>
				<script>$(document).ready(function(e) {
					$('input[name=searchbook]').trigger('keyup');
					refreshChoices();
				});
				</script>
<?php		}
		
			elseif(1==1){
				echo "<center><h3>Choice Filling is Over. Your choices have been automatically confirmed.</h3></center>";
				echo "<center><h4>Your Choices</h4></center>";
				$books = $misc->getChoices($_SESSION['student']); ?>
				<table id="confirmedchoice" border=1 cellspacing="0">
				<tr align="center"><th>Sl. No</th><th>Title</th><th>Author</th></tr>
<?php			while($book = mysqli_fetch_assoc($books)) { ?>
				<tr><td align="center"><?php echo $book['choice_no']; ?></td>
					<td><?php echo $book['title']; ?></td>
					<td><?php echo $book['author']; ?></td>
				</tr>
<?php			} ?>
				</table>
				<center><h4>Result has not yet been declared</h4></center>
<?php		} 

			else{
				echo "<center><h3>You have Confirmed your Choices</h3></center>";
				echo "<center><h4>Your Choices</h4></center>";
				$books = $misc->getChoices($_SESSION['student']); ?>
				<table id="confirmedchoice" border=1 cellspacing="0">
				<tr align="center"><th>Sl. No</th><th>Title</th><th>Author</th></tr>
<?php			while($book = mysqli_fetch_assoc($books)) { ?>
				<tr><td align="center"><?php echo $book['choice_no']; ?></td>
					<td><?php echo $book['title']; ?></td>
					<td><?php echo $book['author']; ?></td>
				</tr>
<?php			} ?>
				</table>
				<center><h4>Result has not yet been declared</h4></center>
<?php		}
		}

		elseif($page == 'notice'){ ?>
			<fieldset class="nobox">
			<legend><h3>ISSUING OF BOOKS</h3>Time : 2.00pm to 5.00pm</legend>
			<table>
			<tr><td style="padding-right:20px">B.tech 7th & 5th sem.(all branches) </td><td>25-08-2014 (MONDAY)</td></tr>
			<tr><td>B.tech 3rd sem.(all branches)</td><td>26-08-2014 (TUESDAY)</td></tr>
			<tr><td>B.tech 1st sem.(Biotech, Chemical, Civil, CS)</td><td>27-08-2014 (WEDNESDAY)</td></tr>
            <tr><td>B.tech 1st sem.(Electrical, EC, IT)</td><td>28-08-2014 (THRUSDAY)</td></tr>
            <tr><td>B.tech 1st sem.(Mechanical, Production)</td><td>29-08-2014 (FRIDAY)</td></tr>
            <tr><td>MCA all sem.</td><td>29-08-2014 (FRIDAY)</td></tr>
			</table>
            <center><h3>Students should bring their ID cards with them.</h3></center>
			</fieldset>
			<h4>Disclaimer</h4>
			<ul>
			<li>By accessing this web site, you are agreeing to be bound by this web site Terms and Conditions of Use.
				If you do not agree with any of these terms, you are prohibited from using or accessing this site.
				The materials contained in this web site are protected by applicable copyright law.
			</li>
			<li>The data regarding the books provided on the site is as provided by the <i>Central library</i> and source of the student's personal and academic data is the <i>Office of the DEAN(ACADEMIC AFFAIRS)</i>.</li>
			<li>The materials on the web site are provided "as is".
				The materials appearing on web site could include technical, typographical, or photographic errors.
				We do not warrant that any of the materials on web site are accurate, complete, or current.
			</li>
			<li>We collect your IP address, pages browsed, your system information ,date and time of your visit and all other activities on this website.	This information is used for internal statistical purposes only.
			</li>
			<li>Visitors are prohibited from violating or attempting to violate the security of the Web site, including, without limitation:
				<ul>
				<li>Accessing data not intended for such user or logging into a server or account which the user is not authorised to access.
				</li>
				<li>Attempt to decompile or reverse engineer any software contained on the web site;</li>                    
				<li>Sell or modify the content of this Web Site or reproduce, display, publicly perform, distribute, or otherwise use the materials in any way for any public or commercial purpose without the respective organisation’s or entity’s written permission.
				</li>         
				</ul>
			</li>
			<li>We may revise these terms of use for its web site at any time without notice. By using this web site you are agreeing to be bound by the then current version of these Terms and Conditions of Use.
			</li>
			</ul>
<?php	}

		elseif($page == 'contact'){ ?>
			<table>
			<tr><td><b>Aayushee Aggrawal</b></td><td><b>Neeraj Kumar</b></td></tr>
			<tr><td>MCA 2nd Yr.</td><td>B.Tech Final Yr.</td></tr>
			<tr><td><a href="mailto:aayusheeagg35@gmail.com">aayusheeagg35@gmail.com</a></td>
				<td><a href="mailto:neerajarya08243@gmail.com">neerajarya08243@gmail.com</td></tr>
			<tr><td>&nbsp;</td> <td>+91-7499983533</td><td></td></tr>
			</table>
<?php	}
		elseif($page == 'password'){
			if($misc->isLoggedIn()){ ?>
				<form action="" method="post" class="changePswd">
				<fieldset class="box">
				<table>
				<tr><td>Old Password</td><td><input type="password" placeholder="Old Password" name="oldpswd" /></td></tr>
				<tr><td>New Password</td><td><input type="password" placeholder="Enter Password" name="newpswd" /></td></tr>
				<tr><td>Confirm Password</td><td><input type="password" placeholder="Confirm Password" name="confpswd" /></td></tr>
				<tr><td colspan="2" align="center"><input type="submit" value="Change Password" /></td></tr>
				<tr><td colspan="2"><span id="msg"></span></td></tr>
				</table>
				</fieldset>
				</form>
<?php		}
			else{ ?>
				Please Contact Webteam to get a new Password.
<?php		}
		}
		elseif($page == 'feecollect'){
			if(!isset($Staff))
				$page = "home";
			else{ ?>
			<center>
			<form method="post" class="feecollect">
			<span style="padding-right:30px">Registration No.: </span>
			<input type="text" placeholder="Enter Regn. No."name="regno" autocomplete="off" />
			<div id="feedetail"></div>
			</form></center>
<?php		}
		}

		elseif($page == 'reprint'){
			if(!isset($Staff))
				$page = "home";
			else{ ?>
			<center>
			<form method="post" class="rereceipt">
			<span style="padding-right:30px">Registration No.: </span>
			<input type="text" placeholder="Enter Regn. No."name="regno" autocomplete="off" />
			<div id="feedetail"></div>
			</form></center>
<?php		}
		}

		elseif($page == 'feecancel'){
			if(!isset($Staff))
				$page = "home";
			else{ ?>
			<center>
			<form method="post" class="feecancel">
			<span style="padding-right:30px">Login Id.: </span>
			<input type="text" placeholder="Enter Login Id"name="regno" autocomplete="off" />
			<div id="feedetail"></div>
			</form></center>
<?php		}
		}
		elseif($page == 'cancelreg')
		{
			if(!isset($Admin) )
				$page = "home";
			else
			{
				?>
				<div id="printcancel">
				<table border="1" align="center"> 
				<tr>
				<th>Reg No.</th><th>Receipt No.</th><th>Date</th><th>Time</th><th>Cancelled By</th><th>Collected By</th>
				</tr>
				<?php
				
					$this->sql->query ="SELECT * FROM cancelled";
					$cancels = $this->sql->process();
					while( $cancel = mysqli_fetch_assoc($cancels))
					{
						?>
						<tr><td><?php echo $cancel['regno']; ?></td><td><?php echo $cancel['rec_no']; ?></td><td><?php echo $cancel['date']; ?></td><td><?php echo $cancel['time']; ?></td><td><?php echo $cancel['cancelled_by']; ?></td><td><?php echo $cancel['collected_by']; ?></td></tr>
						<?php
					}
				?>				
				</table>
				</div><br />
				<center><input type="button" class="cancelprint" value="Print" /></center><br />
				<?php
				
				}
			}
	
		elseif($page == 'bookedit'){
			 if(!isset($Admin) and !isset($Staff))
				$page = "home";
			else{
			?>
			<input type="button" class="bookSwap" value="B.Tech." name="btech" />
			<input type="button" class="bookSwap" value="MCA" name="mca"/>
			<table id="books" class="btech" style="display:none">
<?php		$books = $misc->searchBooks('bt'); ?>
			<thead><tr><th width="370px">Title</th><th width="150px">Author</th><th width="50px">Class</th><th width="65px">Copies</th>
			<th width="65px"></th></tr></thead>
			<tbody>
			<?php
			$bno = 1;
			while($book = mysqli_fetch_assoc($books)) {
				$bno++; ?>
			<tr id="<?php echo "bt-".$book['book_no']; ?>"><td width="370px"><?php echo $book['title']; ?></td>
				<td width="150px"><?php echo $book['author']; ?></td><td width="50px"><?php echo $book['cat']; ?></td>
				<td width="65px"><?php echo $book['rem_copies']; ?></td><td>&nbsp;</td>
				<!--<td width="65px" id="bookedit">Edit</td>-->
				</tr>
			<?php } ?></tbody>
			<tfoot><tr><td width="700px" colspan="4" align="center" style="border-top:1px solid #999">
			<input type="button" class="bookadd" value="Add Book" name="<?php echo "bt-$bno"; ?>" />
			<input type="button" class="booklist" value="Print List" name="bt" /></td></tr></tfoot>
			</table>
			<table id="books" class="mca" style="display:none">
<?php		$books = $misc->searchBooks('mc'); ?>
			<thead><tr><th width="370px">Title</th><th width="150px">Author</th><th width="50px">Class</th><th width="65px">Copies</th>
			<th width="65px"></th></tr></thead>
			<tbody>
			<?php
			$bno = 1;
			while($book = mysqli_fetch_assoc($books)) {
				$bno++; ?>
			<tr id="<?php echo "mc-".$book['book_no']; ?>"><td width="370px"><?php echo $book['title']; ?></td>
				<td width="150px"><?php echo $book['author']; ?></td><td width="50px"><?php echo $book['cat']; ?></td>
				<td width="65px"><?php echo $book['rem_copies']; ?></td><td>&nbsp;</td>
				<!--<td width="65px" id="bookedit">Edit</td>-->
				</tr>
			<?php } ?></tbody>
			<tfoot><tr><td width="700px" colspan="4" align="center" style="border-top:1px solid #999">
			<input type="button" class="bookadd" value="Add Book" name="<?php echo "mc-$bno"; ?>" />
			<input type="button" class="booklist" value="Print List" name="mc" /></td></tr></tfoot>
			</table>
<?php		}
			
		}
		
		elseif($page == 'allot'){
			if(isset($_SESSION['admin'])){
				echo 'Processing....';
				//$misc->allot();
				//$misc->generatetoken();
				//echo 'Done';
			}
			else
				$page = 'home';
		}
		
		elseif($page == 'stats'){
			if(!isset($Admin) and !isset($Staff))
				$page = "home";
			elseif(isset($Staff)){
				$stats = $misc->getStats($Staff['uid']); ?>
				<table>
				<tr><th width="100px">Total Issues</td><td><?php echo $stats['issues']; ?> &nbsp; &nbsp; (
				<?php echo $stats['btcount']; ?> B.Tech + <?php echo $stats['mccount']; ?> MCA )</td></tr>
				<tr><th>Total Cancels</td><td><?php echo $stats['cancels']; ?></td></tr>
				<tr><th>Balance</td><td>Rs. <?php echo $stats['balance']; ?></td></tr>             
				</table>
<?php		}
			else{ ?>
				<div id="print">
				<table style="text-align:left">
				<tr><th colspan=2 align="center" style="font-size:18px"><u>Student's Detail</u></th></tr>

				<tr><td colspan="2"><strong>Program: Bachelor of Technology</strong></td></tr>
<?php			$stat1 = $misc->getStats1('bt');   ?>
				<tr><th>Total Registration</th><td><?php echo $stat1['regn']; ?></td></tr>
				<tr><th>Amount Collected</th><td>Rs. <?php echo $stat1['balance']; ?></td></tr>
				<tr></tr>

				<tr height="20px"><td></td></tr>
				<tr><td colspan="2"><strong>Program: Master of Computer Applications</strong></td></tr>
<?php			$stat2 = $misc->getStats1('mc');   ?>
				<tr><th>Total Registration</th><td><?php echo $stat2['regn']; ?></td></tr>
				<tr><th>Amount Collected</th><td>Rs. <?php echo $stat2['balance']; ?></td></tr>
				<tr></tr><tr></tr>

				<tr height="30px"><td></td></tr>
				<tr><th colspan=2 align="center" style="font-size:18px"><u>Staff's Detail</u></th></tr>

<?php			$staffs = array("lalji","sharad","vinay","lava","vidushi");
				foreach($staffs as $user){
					$stats = $misc->getStats($user);   ?>
					<tr><td colspan="2"><strong>Counter In-charge:
					<?php $info = $misc->getInfo($user,'staff'); echo $info['name']; ?></strong></td></tr>
					<tr><th>Total Issues</th><td><?php echo $stats['issues']; ?></td></tr>
					<tr><th>Total Cancels</th><td><?php echo $stats['cancels']; ?></td></tr>
					<tr><th>Balance</th><td>Rs. <?php echo $stats['balance']; ?></td></tr>
					<tr height="20px"><td></td></tr>
<?php			} ?>
				<tr><th>Total Amount Collected</th><td style="border:#000 1px solid" align="center">
				Rs. <?php echo $stat1['balance']+$stat2['balance']; ?></td></tr>
				<tr>&nbsp;</tr><tr>&nbsp;</tr>
                <tr><td>Dated: <?php $date=date('d/M/y'); $time=date('H:i:s');
				echo $date; 
				?>  </td></tr><br /><tr><td>Time: <?php echo $time; ?></td></tr>  
				</table>
				</div>
				<center><input type="button" class="stats" value="Print" /></center><br />
<?php
			}
		}
		
		elseif($page == 'print'){
			if(isset($_SESSION['admin'])){ ?>
				<center>
				<input type="button" class="print" value="Book List" onclick="location.href='bookedit'" /><br /> <br/>
				<input type="button" class="print" value="Collection Report" onclick="location.href='stats'" /><br /> <br/>
				<input type="button" class="print" value="View Cancel Reg. " onclick="location.href='cancelreg'" /><br /><br/>
				<input type="button" class="print" value="Token List" onclick="location.href='tokenlist'" /><br /> <br/>
				<input type="button" class="print" value="Individual Token" onclick="location.href='tokens'" /><br /><br/>
<?php		}
			else
				$page = 'home';
		}

		elseif($page == 'tokenlist'){
			if(isset($_SESSION['admin'])){
				$progs=array('bt','mc'); ?>
				<table width="600px">
				<tr><th width="120px" valign="top">Program</th>
				<td>
<?php			foreach($progs as $prog){ ?>
					<input type="radio" name="prog" value="<?php echo $prog; ?>" id="<?php echo $prog; ?>" />
					<label for="<?php echo $prog; ?>"><?php echo $misc->getCourseName($prog); ?></label><br />
<?php			} ?>
				</td></tr>
				<tr class="sem" style="display:none"><th valign="top">Semester</th>
<?php			foreach($progs as $prog){ ?>
					<td id="<?php echo $prog; ?>" class="sem" style="display:none">
<?php				$sems = $misc->getSems($prog);
					while($sem = mysqli_fetch_assoc($sems)){ ?>
						<input type="checkbox" name="<?php echo $prog; ?>-sem" value="<?php echo $sem['sem']; ?>" id="sem-<?php echo $sem['sem']; ?>" />
						<label for="sem-<?php echo $sem['sem']; ?>"><?php echo $sem['sem']; ?></label><br />
<?php				} ?>
					</td>
<?php			} ?>
				</tr>
				<tr class="bra" style="display:none"><th valign="top">Branch</th>
<?php			foreach($progs as $prog){ ?>
					<td id="<?php echo $prog; ?>" class="bra" style="display:none">
<?php				$bras = $misc->getBras($prog);
					while($bra = mysqli_fetch_assoc($bras)){ ?>
						<input type="checkbox" name="<?php echo $prog; ?>-bra" value="<?php echo $bra['bra']; ?>" id="bra-<?php echo $bra['bra']; ?>" />
						<label for="bra-<?php echo $bra['bra']; ?>"><?php echo $bra['branch']; ?></label><br />
<?php				} ?>
					</td>
<?php			} ?>
				</tr>
				<tr class="printbtn" style="display:none"><td colspan="2" align="center">
				<input type="button" class="tokenlist" value="Print" /></td></tr>
				</table>
<?php		}
			else
				$page = 'home';
		}
				
		elseif($page == 'tokens'){
			if(isset($_SESSION['admin'])){
				$progs=array('bt','mc'); ?>
				<table width="600px">
				<tr><th width="120px" valign="top">Program</th>
				<td>
<?php			foreach($progs as $prog){ ?>
					<input type="radio" name="prog" value="<?php echo $prog; ?>" id="<?php echo $prog; ?>" />
					<label for="<?php echo $prog; ?>"><?php echo $misc->getCourseName($prog); ?></label><br />
<?php			} ?>
				</td></tr>
				<tr class="sem" style="display:none"><th valign="top">Semester</th>
<?php			foreach($progs as $prog){ ?>
					<td id="<?php echo $prog; ?>" class="sem" style="display:none">
<?php				$sems = $misc->getSems($prog);
					while($sem = mysqli_fetch_assoc($sems)){ ?>
						<input type="checkbox" name="<?php echo $prog; ?>-sem" value="<?php echo $sem['sem']; ?>" id="sem-<?php echo $sem['sem']; ?>" />
						<label for="sem-<?php echo $sem['sem']; ?>"><?php echo $sem['sem']; ?></label><br />
<?php				} ?>
					</td>
<?php			} ?>
				</tr>
				<tr class="bra" style="display:none"><th valign="top">Branch</th>
<?php			foreach($progs as $prog){ ?>
					<td id="<?php echo $prog; ?>" class="bra" style="display:none">
<?php				$bras = $misc->getBras($prog);
					while($bra = mysqli_fetch_assoc($bras)){ ?>
						<input type="checkbox" name="<?php echo $prog; ?>-bra" value="<?php echo $bra['bra']; ?>" id="bra-<?php echo $bra['bra']; ?>" />
						<label for="bra-<?php echo $bra['bra']; ?>"><?php echo $bra['branch']; ?></label><br />
<?php				} ?>
					</td>
<?php			} ?>
				</tr>
				<tr class="printbtn" style="display:none"><td colspan="2" align="center">
				<input type="button" class="tokens" value="Print" /></td></tr>
				</table>
<?php		}
			else
				$page = 'home';
		}
		
/*		elseif($page == 'manage'){
				if(isset($_SESSION['admin'])){?>
					<table border =1>
						<tr><th>NAME</th><th>TOTAL ISSUES</th><th>CANCEL</th><th>B.TECH</th>
						<th>MCA</th><th>BALANCE</th><th>FEE</th></tr>
					<?php 
					$totalissue=$totalcan=$totalbt=$totalmc=$totalbal=0;
					$this->sql->query = "SELECT uid,name from staff where designation = 'Library Staff'";
					$result=$this->sql->process();
					while($staff = mysqli_fetch_assoc($result))
					{
					$stats = $misc->getStats($staff['uid']);
					
					echo "<tr><td>".$staff['name']."</td><td>".$stats['issues']."</td><td>".$stats['cancels']."</td>
					<td>".$stats['btcount']."</td><td>".$stats['mccount']."</td><td>"."&#8377  ".$stats['balance']."</td>
					<td>"."&#8377 15"."</td></tr>";
						$totalissue+=$stats['issues'];
						$totalcan +=$stats['cancels'];
						$totalbt+=$stats['btcount'];
						$totalmc +=$stats['mccount'];
						$totalbal +=$stats['balance'];
					}?>
						<tr><th><b>TOTAL</b></th><td><b><?php echo $totalissue;?></b></td><td><b><?php echo $totalcan;?>
						</b></td><td><b><?php echo $totalbt;?></b></td><td><b><?php echo $totalmc;?></b></td><td><b>&#8377
						&nbsp; <?php echo $totalbal;?></b></td><td><b>&#8377 &nbsp; 15</b></td></tr>
					</table> <br/>                       
					 <center>
					 <a href="#"><input type="submit" value="PRINT STATS" style="height:30px; "/></a>
					&nbsp; &nbsp; &nbsp;
					</center><br/>
					<a href="printlist"><input type="submit" value="PRINT TOKENLIST" style="height:30px; "/></a>
					&nbsp;&nbsp; &nbsp;
					<a href="printform"><input type="submit" value="PRINT BOOKSALLOTED" style="height:30px; "/></a>
					&nbsp; &nbsp; &nbsp;
					<br /><br />
			  <?php  }
				  else $page = 'home';
		   } 
*/

/**		elseif($page == 'flushdb'){
			if(isset($_SESSION['admin']))
				$misc->flushdb();
			else
				$page = 'home';
		}*/

/** ----------------------------------------- Default Home Page -----------------------------------*/

		if($page == 'home'){
			if(isset($_SESSION['student'])){ ?>
				<fieldset class="nobox">
				<table>
				<tr><td width="100px">Name</td><td width="250px"><?php echo $Student['name']; ?></td></tr>
				<tr><td>Course</td><td><?php echo $Student['course']; ?></td></tr>
				<tr><td>Branch</td><td><?php echo $Student['branch']; ?></td></tr>
				<tr><td>Semester</td><td><?php echo $Student['sem']; ?></td></tr>
				<tr><td>CPI</td><td><?php echo $Student['cpi']; ?></td></tr>
				<tr><td>DOB</td><td><?php echo date('jS F, Y',strtotime($Student['dob'])); ?></td></tr>
				<tr><td>Email Id</td><td><?php echo $Student['email']; ?></td><!--<td id="update">Edit</td>--></tr>
				<tr><td>Mobile</td><td><?php echo $Student['mobile']; ?></td><!--<td id="update">Edit</td>--></tr>
				<tr><td>Blood Group</td><td><?php echo $Student['blood_grp']; ?></td><!--<td id="update">Edit</td>--></tr>
				<tr><td colspan="2" height="50px" align="center"><a href="<?php echo $base; ?>choice"><b>Proceed</b></a></td></tr>
				</table>
				</fieldset>
<?php		}
			elseif(isset($_SESSION['admin'])){ ?>
				<fieldset class="nobox">
				<table>
				<tr><td width="100px">Name</td><td width="250px"><?php echo $Admin['name']; ?></td></tr>
				<tr><td>Designation</td><td><?php echo $Admin['designation']; ?></td></tr>
				<tr><td>Email Id</td><td><?php echo $Admin['email']; ?></td></tr>
				<tr><td>Mobile</td><td><?php echo $Admin['mobile']; ?></td></tr>
				<tr><td colspan="2" align="center"><input type="button" value="Proceed" /></td></tr>
				</table>
				</fieldset>
<?php		}
			elseif(isset($_SESSION['staff'])){ ?>
				<fieldset class="nobox">
				<table>
				<tr><td width="100px">Name</td><td width="250px"><?php echo $Staff['name']; ?></td></tr>
				<tr><td>Designation</td><td><?php echo $Staff['designation']; ?></td></tr>
				<tr><td>Email Id</td><td><?php echo $Staff['email']; ?></td></tr>
				<tr><td>Mobile</td><td><?php echo $Staff['mobile']; ?></td></tr>
				<tr><td colspan="2" height="50px" align="center"><a href="<?php echo $base; ?>feecollect"><b>Proceed</b></a></td></tr>
				</table>
				</fieldset>
<?php		}
			else{ ?>
				<form action="" method="post" class="login">
				<fieldset class="box">
				<table>
				<tr><td>Login Id</td><td><input type="text" placeholder="Enter Id" name="loginid" /></td></tr>
				<tr><td>Password</td><td><input type="password" placeholder="Enter Password" name="passwd" /></td></tr>
				<tr><td colspan="2" align="center"><input type="submit" value="Login" />
				<input type="button" value="Forgot Password"  onclick="location.href='password'" /></td></tr>
				<tr><td colspan="2"><span id="msg"></span></td></tr>
				</table>
				</fieldset>
				</form>
				<center><strong><h3> RESULT DECLARED </h3></strong></center>

<?php		}
		}
	}
}