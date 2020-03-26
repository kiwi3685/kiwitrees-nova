<div id="content">
<h3>MyOOS [Dumper] based on MySQLDumper 1.24.4</h3>

<h3>Σχετικά με το εργαλείο</h3>
Η ιδέα για το εργαλείο αυτό είναι του Daniel Schlichtholz.<p>Το 2004 δημιούργησε ένα φόρουμ με όνομα MySQLDumper και σύντομα, προγραμματιστές που ασχολούνται με νέα σκριπτ, ενίσχυσαν τα σκριπτ του Daniel.<br>Μέσα σε λίγο καιρό το μικρό του προγραμματάκι μετατράπηκε σε ένα σταθερότατο εργαλείο.<p>Εαν έχετε ιδέες για βελτίωση επισκευθείτε το φόρουμ  του MySQLDumper: <a href="https://foren.myoos.de/viewforum.php?f=41" target="_blank">https://foren.myoos.de/viewforum.php?f=41</a>.<p>Σας ευχόμαστε ατελείωτα αντίγραφα ασφαλείας με αυτό το εργαλείο.<br><br><h4>Η ομάδα του MySQLDumper</h4>
<table><tr><td><img src="images/logo.gif" alt="MySQLDumper" border="0"></td><td valign="top">
Daniel Schlichtholz</td></tr></table>
<br /> Greek translation by Anthony Vasileiou (<a href="http://thraki.info" target="_blank">diastasi</a>)

<h3>Βοήθεια MySQLDumper</h3>

<h4>Μεταφόρτωση</h4>
Το Script είναι διαθέσιμο στην ιστοσελίδα του MySQLDumper.<br>
Προτείνουμε να επισκεφτεστε συχνά την Ιστοσελίδα για τις τελευταίες πληροφορίες, αναβαθμίσεις και βοήθεια.<br>
Η διεύθυνση είναι <a href="https://foren.myoos.de/viewforum.php?f=41" target="_blank">
https://foren.myoos.de/viewforum.php?f=41
</a>

<h4>Ελάχιστες απαιτήσεις</h4>
Το Script λειτουργεί σχεδόν σε κάθε σύστημα διακομιστή (Windows, Linux, ...) <br>
με PHP >= Version 4.3.4 και GZip-Library, MySQL (>= 3.23), JavaScript (πρέπει να είναι ενεργό).

<a href="install.php?language=el" target="_top"><h4>Εγκατάσταση</h4></a>
Η εγκατάσταση είναι πολύ απλή.
Αποσυμπιέστε το αρχείο σε οποιονδήποτε φάκελο,
που είναι διαθέσιμος από το Webserver<br>
(π.χ. στο ριζικό κατάλογο [Server rootdir/]MySQLDumper)<br>
αλλάξτε το config.php σε chmod 777<br>
... έγινε!<br>
τώρα ξεκινήστε το MySQLDumper στον πλοηγό σας πληκτρολογώντας "http://istoselida/MySQLDumper"
για να ολοκληρωθεί η εγκατάσταση, και απλά ακολουθήστε τις οδηγίες.

<br><b>Σημείωση:</b><br><i>Εαν ο webserver σας τρέχει με την επιλογή safemode=ON το MySqlDump δεν πρέπει να δημιουργεί καταλόγους.<br>
Πρέπει να τους κάνετε μόνοι σας.<br>
Το MySqlDump σταματάει σε αυτό το σημείο και σας λέει τι να κάνετε.<br>
Αφού δημιουργήσετε τους καταλόγους το MySqlDump θα λειτουργήσει κανονικά.</i><br><br>

<a name="perl"></a><h4>Οδηγός για το Perl script</h4>

Οι περισσότεροι έχουν ένα κατάλογο cgi-bin, μέσα στον οποίο μπορεί το Perl να εκτελεστεί. <br>
Συνήθως βρίσκεται στο http://www.example.com/cgi-bin/ . <br><br>

Κάντε τα παρακάτω βήματα για αυτή την περίπτωση.  <br><br>

1.  Πατήστε στο MySQLDumper τον πλήκτρο Αντίγραφα Ασφαλείας και κατόπιν πιέστε το "Αντίγραφα Ασφαλείας Perl"   <br>
2.  Αντιγράψτε τη διαδρομή που θα βρείτε κάτω από το "Εισαγωγή στο crondump.pl για absolute_path_of_configdir":    <br>
3.  Ανοίξτε το αρχείο "crondump.pl" σε έναν επεξεργαστή κειμένου <br>
4.  Επικολλήστε την αντεγραμένη διαδρομή στο σημείο με το absolute_path_of_configdir (χωρίς κενά) <br>
5.  Αποθηκεύστε το crondump.pl <br>
6.  Αντιγράψτε το crondump.pl, το perltest.pl και το simpletest.pl στο κατάλογο cgi-bin (ASCII mode στο ftp-client!) <br>
7.  Δώστε chmod 755 στα scripts.  <br>
7b. Εάν επιθυμείτε κατάληξη cgi, αλλάξτε τις καταλήξεις και από τα 3 αρχεία pl - > cgi (μετονομασία)  <br>
8.  Πηγαίνετε στο MySQLDumper στη σελίδα "Ρυθμίσεις"<br>
9.  Κάντε κλικ στο "Cronscript" <br>
10. Αλλάξτε τη διαδρομή εκτέλεσης του Perl σε /cgi-bin/<br>
10b. Εάν τα Scripts έχεουν μετονομαστεί σε *.cgi , αλλάξτε την κατάληξη αρχείου σε cgi <br>
11  Αποθηκεύστε τις Ρυθμίσεις <br><br>

Ετοιμοι ! Τα scripts είναι διαθέσιμα από τη σελίδα "Αντίγραφα Ασφαλείας" <br><br>

Οταν μπορείτε να εκτελέσετε Perl, χρειάζονται μόνο τα παρακάτω βήματα:  <br><br>

1.  Πηγαίνετε στο MySQLDumper στη σελίδα "Αντίγραφα Ασφαλείας".  <br>
2.  Αντιγράψτε τη διαδρομή που θα βρείτε κάτω από το "Εισαγωγή στο crondump.pl για absolute_path_of_configdir":  <br>
3.  Ανοίξτε το αρχείο "crondump.pl" σε έναν επεξεργαστή κειμένου <br>
4.  Επικολλήστε την αντεγραμένη διαδρομή στο σημείο με το absolute_path_of_configdir (χωρίς κενά) <br>
5.  Αποθηκεύστε το crondump.pl <br>

6.  Δώστε chmod 755 στα scripts.  <br> 
6b. Εάν επιθυμείτε κατάληξη cgi, αλλάξτε τις καταλήξεις και από τα 3 αρχεία pl - > cgi (μετονομασία)  <br>
    (προχωρήστε στα βήματα 10b+11 παραπάνω) <br><br>


Οι χρήστες Windows πρέπει να αλλάξουν την πρώτη γραμμή σε όλα τα Perlscripts, στη διαδρομή του Perl.  <br><br>

Παράδειγμα:  <br>

αντί για :  #!/usr/bin/perl w <br>
τώρα #!C:\perl\bin\perl.exe w<br><br>

<h4>Λειτουργία</h4><ul>

<h6>Μενού</h6>
Εδω επιλέγετε τη Β.Δεδομένων σας από το μενού πολλαπλών επιλογών "Επιλογή Β.Δεδομένων".<br>
Ολες οι παραπάνω επιλογές αναφέρονται στην επιλεγμένη Β.Δεδομένων.

<h6>Αρχική</h6>
Εδώ βλέπετε πληροφορίες για το σύστημα σας, τις εκδόσεις και λεπτομέρειες σχετικά με τις Β.Δεδομένων σας.<br>
Εαν κάνετε κλικ σε μία Β.Δεδομένων στον πίνακα, θα πάρετε μία λίστα με εγγραφές με αριθμήσεις εγγραφών, μέγεθος και τελευταία επεξεργασία.

<h6>Ρυθμίσεις</h6>
Εδώ επεξεργάζεστε τις ρυθμίσεις σας, τις αποθηκεύετε ή φορτώνετε τις προεπιλεγμένες.
<ul>
	<li><a name="conf1"><strong>Λίστα Β.Δεδομένων:</strong> Η λίστα των Β.Δεδομένων σας. Η ενεργή Β.Δεδομένων είναι έντονη.</li>
	<li><a name="conf2"><strong>Πρόθεμα πίνακα:</strong> Εδώ επιλέγετε ένα πρόθεμα πίνακα για κάθε Β.Δεδομένων χωριστά. Το πρόθεμα είναι ένα φίλτρο, που διαχειρίζεται τους πίνακες σε ένα αντίγραφο ασφαλείας, που ξεκινάει με αυτό το  πρόθεμα (π.χ. όλοι οι πίνακες ξεκινούν με "phpBB_"). Εαν δε θέλετε να το χρησιμοποιήσετε, αφήστε το πεδίο κενό.</li>
	<li><a name="conf3"><strong>Συμπίεση GZip:</strong> Εδώ ενεργοποιείτε τη συμπίεση. Προτείνεται να εργάζεστε με την συμπίεση ενεργοποιημένη, για μικρότερο μέγεθος αρχείων, ώστε να μην πιάνουν πολύ χώρο στο δίσκο.</li>
	<li><a name="conf19"></a><strong>Αριθμός εγγραφών για αντίγραφα ασφαλείας:</strong> Ο αριθμός των εγγραφών που διαβάζονται ταυτόχρονα κατά τη λειτουργία αντιγράφων ασφαλείας, πριν το script κάνει callback. Για αργούς διακομιστές μειώστε την παράμετρο για να αποφύγετε timeouts.</li>
	<li><a name="conf20"></a><strong>Αριθμός εγγραφών για επαναφορά:</strong> Ο αριθμός των εγγραφών που διαβάζονται ταυτόχρονα κατά την επαναφορά, πριν το script κάνει callback. Για αργούς διακομιστές μειώστε την παράμετρο για να αποφύγετε timeouts.</li>
	<li><a name="conf4"></a><strong>Κατάλογος για τα αρχεία αντιγράφων ασφαλείας:</strong> επιλέξτε τον κατάλογο για τα αντίγραφα ασφαλείας. Εάν επιλέξετε έναν νέο, Το script θα τον δημιουργήσει για σας. Μπορείτε να χρησιμοποιήσετε σχετικές ή απόλυτες διαδρομές.</li>
	<li><a name="conf5"></a><strong>Αποστολή Αντιγράφου ασφαλείας ως email:</strong> Οταν έχετε αυτή την επιλογή, το script θα στείλει αυτόματα το ολοκληρωμένο αντίγραφο ασφαλείας μέσω email με συννημένο (Προσέξτε!, πρέπει να χρησιμοποιείτε συμπίεση με αυτή την επιλογή επειδή το αντίγραφο ασφαλείας μπορεί να είναι τεράστιο για email!)</li>
	<li><a name="conf6"></a><strong>Διεύθυνση Email:</strong> Διεύθυνση email παραλήπτη</li>
	<li><a name="conf7"></a><strong>Θέμα Email:</strong> Το θέμα του email</li>
	<li><a name="conf13"></a><strong>Μεταφορά FTP: </strong>Οταν έχετε αυτή την επιλογή, το script θα στείλει αυτόματα το ολοκληρωμένο αντίγραφο ασφαλείας μέσω FTP.</li>
	<li><a name="conf14"><strong>Διακομιστής FTP: </strong>Η διεύθυνση του διακομιστή FTP (π.χ. ftp.mybackups.de)</li>
	<li><a name="conf15"></a><strong>Θύρα διακομιστή FTP: </strong>Η θύρα για τον διακομιστή FTP (συνήθως 21)</li>
	<li><a name="conf16"></a><strong>Χρήστης FTP: </strong>Το όνομα χρήστη του λογαριασμού FTP</li>
	<li><a name="conf17"></a><strong>Κωδικός FTP: </strong>Ο κωδικός του λογαριασμού FTP</li>
	<li><a name="conf18"></a><strong>Κατάλογος φόρτωσης FTP: </strong>Ο κατάλογος που αποθηκεύονται τα αντίγραφα ασφαλείας (πρέπει να έχει δικαιώματα για φόρτωση!)</li>
	
	<li><a name="conf8"></a><strong>automatic deletion of backups:</strong> When you activate this options, backup files will be deleted automatically by the following rules.</li>
	<li><a name="conf10"></a><strong>Delete by number of files:</strong> A Value > 0 deletes all files except the given value</li>
	<li><a name="conf11"></a><strong>Langauge:</strong> choose your language for the interface.</li>
</ul>

<h6>Διαχείριση Αρχείων</h6>
Ολες οι ενέργειες βρίσκονται εδώ.<br>
Εδώ θα δείτε όλα τα αρχεία αντιγράφων ασφαλείας που βρίσκονται στον κατάλογο backup.
Για την "Επαναφορά" και "Διαγραφή" πρέπει να επιλέξετε πρώτα ένα αρχείο.
<UL>
	<li><strong>Επαναφορά:</strong> επαναφέρετε τη Β.Δεδομένων σας με εγγραφές απο το επιλεγμένο αντίγραφο ασφαλείας.</li>
	<li><strong>Διαγραφή:</strong> διαγράφετε το επιλεγμένο αντίγραφο ασφαλείας.</li>
	<li><strong>Νέο αντίγραφο ασφαλείας:</strong> εδώ μπορείτε να ξεκινήσετε ένα νέο αντίγραφο ασφαλείας (dump) με τις περαμέτρους που έχετε ορίσει.</li>
</UL>

<h6>Καταγραφές</h6>
Εδώ βλέπετε όλες τις εγγραφές στο αρχείο καταγραφών και μπορείτε να τις διαγράψετε.

<h6>Επαινοι / Βοήθεια</h6>
Αυτή η σελίδα.
</ul>