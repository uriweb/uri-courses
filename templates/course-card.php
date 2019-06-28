<?php
/**
 * HTML for the courses
 * sample object:
 
 stdClass Object
(
    [id] => 869
    [Eff_Date] => 3/1/2016
    [Acad_Group] => A_SCI
    [College_Name] => College of Arts and Sciences
    [Acad_Org] => COMMUN_STD
    [FormalDesc] => Communication Studies
    [Subject] => COM
    [Catalog] => 100
    [Course_id] => 214707
    [Long_Title] => Communication Fundamentals
    [Min_Units] => 3
    [Max_Units] => 3
    [Component] => LEC
    [Designation] => GE
    [Descr] => (3 crs.) Integrates basic theory and experience in a variety of communication contexts including public speaking, small groups, and interpersonal communication. Examines human differences in order to develop more effective communication skills. Not open to students with credit in 110.  (Lec. 3) (B2)
    [Status] => A
    [Sch_Print] => Y
    [Cat_Print] => Y
    [Rq_Group] => 0
)
 
 */	
 
	$course = $args;
	
?><div class="course">
	<header>
		<div class="header">
			<h3><span class="course-subject-code"><?php print $course->Subject; ?></span><span class="course-number"><?php print $course->Catalog; ?></span><span class="course-separator">: </span><span class="course-title"><?php print $course->Long_Title; ?></span></h3>
			<?php
			/*
			<div class="course-meta"><span class="course-college"><?php print $course->College_Name; ?></span><span class="course-separator">: </span><span class="course-subject"><?php print $course->FormalDesc; ?></span></div>
			*/
			?>
		</div>
	</header>
	<div class="description course-description">
		<?php print $course->Descr; ?>
	</div>
</div>
