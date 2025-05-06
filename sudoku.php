<?php
/*mala napomena u samome startu :) kada se izvrši opcija "Želim sve ispočetka!" broj pokušaja se
    vraća na 0, u prvobitnom planu je bilo kao da će se broj pokušaja povećati za 1, ali onda sam
    vidio na internetu sudoku 6x6 s vremenom i također opciju restart, koja u ovom slučaju isto obriše sve
    ali vrati vrijeme na 00:00, tako da sam i ja ovdje vraćo na 0 kao broj pokušaja.
    A svaka ostala interakcija će povećati za 1 broj pokušaja*/
class RijesiSudoku
{
    protected $imeIgraca, $brojPokusaja, $gameOver;
    protected $errorMsg;
    protected $ploca,$igraca_ploca, $rjesenje,$pocetne_boje, $boje;

    const RAZLICITI_SU = -1;
    
    const PODUDARAJU_SE = 1;

    function __construct()
    {
        $this->ploca = [
            ['','',4,'','',''],
            ['','','',2,3,''],
            [3,'','','',6,''],
            ['',6,'','','',2],
            ['',2,1,'','',''],
            ['','','',5,'','']
        ];

        $this->igraca_ploca = $this->ploca;

        // 0 - praznina, 1 - pocetno, 2 - plava, 3 - crvena, 4 - kasnije u kodu - samo da nije 0 ili 1
        $this->pocetne_boje = array();
        for($i = 0; $i<6; $i++){
            for($j = 0; $j < 6;$j++){
                $this->pocetne_boje[$i][$j] = ($this->igraca_ploca[$i][$j] != '') ? 1 : 0;
            }
        }

        $this->boje = array();
        $this->boje = $this->pocetne_boje;

        //sastavimo kako bi trebalo naše rješenje izgledati od plavih dobro unesenih brojeva i pocetnih u crnoj boji brojeva
        $this -> rjesenje = array();
        for($i = 0; $i<6; $i++){
            for($j = 0; $j < 6;$j++){
                $this->rjesenje[$i][$j] = ($this->pocetne_boje[$i][$j] !== 1) ? 2 : 1;
            }
        }

        $this->imeIgraca = false;
        $this->brojPokusaja = -1;
        $this->gameOver = false;
        $this->errorMsg = false;
    }

    function IspisiFormuZaIme()
    {
       ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Sudoku 6x6 - Dobrodošli </title>
        </head>
        <body>
            <h1>Sudoku 6x6!</h1>
            <!-- forma za unos koja se vraca ponovno na ovu stranicu -->
            <form method ="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
                Unesi svoje ime: <input type="text" name="imeIgraca" />
                <button type="submit">Započni igru!</button>
            </form>
            <?php if( $this->errorMsg !== false ) echo '<p>Greška: ' . htmlentities( $this->errorMsg ) . '</p>'; ?>
        </body>
        </html>

    <?php       
    }
    
function isValidNumber($board, $r, $s, $br) {
    // Provjerava je li broj već prisutan u retku ili stupcu
    for ($i = 0; $i < 6; $i++) {
        if (($board[$r][$i] === $br && $s !== $i) || ($board[$i][$s] === $br && $r !== $i)) {
            return false;
        }
    }

    // Provjeri je li broj već prisutan u istom podmatrici dimenzija 2x3
    $startR = $r - ($r % 2);
    $startS = $s - ($s % 3);
    for ($i = 0; $i < 2; $i++) {
        for ($j = 0; $j < 3; $j++) {
            if ($board[$i + $startR][$j + $startS] === $br && ($i+$startR)!== $r && ($j+$startS) !==$s) {
                return false;
            }
        }
    }
    //sve je dobro prošlo vraćamo true
    return true;
}

function ispisiFormuZaRjesavanjeSudoku($ploca){
        ++$this->brojPokusaja;
        ?>
        <!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sudoku 6x6</title>
    <link rel="stylesheet" href="sudoku.css" />
</head>
<body>
    <h1>Sudoku 6x6!</h1>

    Igrač:  <?php echo htmlentities( $this->imeIgraca ); ?> 
    <br>
    Broj pokušaja: <?php echo htmlentities( $this->brojPokusaja ); ?> 
    <br><br>
    
    <form method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" >

    <table> 
        <?php
        //SVUGDJE GDJE SE POJAVLJUJE if($br_s === 2) provjerava se je li do unutarnjeg obruba koji treba biti podebljan za prikaz odg. sudokua 6x6
        for($br_r = 0; $br_r <6 ; $br_r++){
            if($br_r === 1 || $br_r === 3)
                echo '<tr class="horizontalno">'; //ako je rijec 2. ili 4. retku podebljamo crte za odgovarajuci izgled 6x6 sudokua
            else
                echo '<tr>';
            for($br_s = 0; $br_s < 6; ++$br_s){
                //gledamo ako je pocetan broj, moze se i pomocu ploce s bojama, ali moze se i pomocu pocetne ploce
                if($this->ploca[$br_r][$br_s] !== ''){
                    //provjere radi obruba
                    if($br_s === 2){
                            echo '<td class = "pocetno_vodoravno">' . $this->ploca[$br_r][$br_s] .'</td>';
                            continue;
                        }
                        else{
                            echo '<td class = "pocetno">' . $this->ploca[$br_r][$br_s] .'</td>';
                            continue;
                        } 
                }
                if($this->igraca_ploca[$br_r][$br_s] === '')
                {
                    if($br_s === 2)
                    {
                        //ako je celija ne sadrzi broj stvaramo textbox i provjeravamo koji sve brojevi mogu ići u ispis ispod    
                        echo "<td style = 'border-right: 3px solid black;'><input type='text' name='polje[$br_r][$br_s]' size='1' maxlength='1' style='vertical-align: bottom;'>
                        <small class = 'slova_u_celijama'>";

                        for ($broj = 1; $broj <= 6; $broj++) {
                            if ($this->isValidNumber($this->igraca_ploca, $br_r, $br_s, $broj))
                            {
                                echo $broj; //ispisujemo te brojeve ispod text prostora
                            }
                        }
                        echo  "</small> </td>";
                    }
                    
                    else
                    {
                        //ako je celija ne sadrzi broj stvaramo textbox i provjeravamo koji sve brojevi mogu ići u ispis ispod    
                        echo "<td><input type='text' name='polje[$br_r][$br_s]' size='1' maxlength='1' style='vertical-align: bottom;'>
                        <small class = 'slova_u_celijama'>";

                        for ($broj = 1; $broj <= 6; $broj++) {
                            if ($this->isValidNumber($this->igraca_ploca, $br_r, $br_s, $broj))
                            {
                                echo $broj; //ispisujemo te brojeve ispod text prostora
                            }
                        }
                        echo  "</small> </td>";
                    }        
                }
                
                else
                {
                    if($this->igraca_ploca[$br_r][$br_s] !== ''){
                        //ovisno o boji unesenih brojeva, tj. oznakama boje obojimo ih u sudoku celijama
                        if($this->boje[$br_r][$br_s] === 2)
                        {
                            if($br_s === 2)
                            {
                                echo '<td class="dobro_vodoravno">'. $this->igraca_ploca[$br_r][$br_s] .'</td>';
                                continue;
                            }
                            else
                            {
                                echo '<td class="dobro">'. $this->igraca_ploca[$br_r][$br_s] .'</td>';
                                continue;
                            }
                        }
                        else if($this->boje[$br_r][$br_s] === 3)
                        {
                            if($br_s === 2)
                            {
                                echo '<td class="krivo_vodoravno">'. $this->igraca_ploca[$br_r][$br_s] .'</td>';
                                continue;
                            }
                            else
                            {
                                echo '<td class="krivo">'. $this->igraca_ploca[$br_r][$br_s] .'</td>';
                                continue;
                            }
                        }
                    }
                }
            }
            echo '</tr>';
        }
        ?>
    </table>
    <!--ODABIR OPCIJE: ZA UNOS, BRISANJE, VRACANJE NA POCETAK -->
    <br>
        <input type="radio" name="opcija"  value="unesi_broj"> Unos brojeva pomoću textboxeva. 
        <br><br>
        <input type="radio" name="opcija" value="obrisi_broj">Obriši broj iz retka
            <select name="izbrisi_redak" id="izbrisi_redak">
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
                <option value="6">6</option>
            </select>
            i stupca
            <select name="izbrisi_stupac" id="izbrisi_stupac">
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
                <option value="6">6</option>
            </select>
        <br><br>
        <label for="ispocetka"><input type="radio" name="opcija" id="ispocetka" value="reset_sudoku"/>
            Želim sve ispočetka!
        </label>

        <br><br>
        
        <button type="submit">Izvrši akciju!</button>
    </form>  
    <?php if( $this->errorMsg !== false ) echo '<p>Greška: ' . htmlentities( $this->errorMsg ) . '</p>'; ?>
</body>
</html>
<?php 

    }

    function get_imeIgraca()
    {
        //Imamo li već definirano ime igrača
        if($this->imeIgraca !== false)
            return $this->imeIgraca;

        //Možda se trenunto šalje
        if(isset($_POST['imeIgraca']))
        {
            //Provjera sastoji li se ima samo od slova
            if( !preg_match( '/^[a-zA-ZčČćĆđĐšŠžŽ]{2,20}$/', $_POST['imeIgraca'] ) )
			{
				// Ako nije dobro, javimo odgovarajuću grešku
				$this->errorMsg = 'Ime igrača treba imati između 2 i 20 slova.';
				return false;
			}
            else
            {
                // Spremamo ime u objekt
                $this->imeIgraca = $_POST['imeIgraca'];
                return $this->imeIgraca;
            }
        }
        // U slučaju da nismo ništa primili
        return false;
    }

    function obradiPokusaj()
    {
        if (isset($_POST['opcija']))
        {
            $odabir = $_POST['opcija'];
    
            if($odabir === "unesi_broj")
            {
                $sudokuGrid = $_POST['polje'];
                //moramo napraviti for petlju da vidimo koji su brojevi uneseni!
                for($i=0; $i < 6; $i++){
                    for($j=0; $j < 6; $j++){
                        if(isset($sudokuGrid[$i][$j]))
                        {
                            if(!(preg_match('/^[1-6]?$/',$sudokuGrid[$i][$j]))) 
                            {
                                $this->errorMsg = 'Provjeri svoj unos, svi brojevi trebaju biti između 1 i 6 :)';
                                return false;
                            }
                        }
                    }
                }
                //ako je sve dobro proslo dodajemo brojeve na igraču ploču i u boje spremamo opciju 4, tek toliko da izbjegnemo 1 i 0 za preskakanje
                for($i=0; $i < 6; $i++){
                    for($j=0; $j < 6; $j++){
                        if(isset($sudokuGrid[$i][$j]))
                        {
                            $x = (int) $sudokuGrid[$i][$j];
                            if($x === 0)
                                continue; //ako na toj poziciji nema unesenog broja nastavljamo dalje na iduću
                    
                            $this->igraca_ploca[$i][$j] = $x;
                            $this->boje[$i][$j] = 4;
                        }
                    }
                }
                
        //nadalje gledamo novo spremljene brojeve, preskacemo pocetne oznacene s 1 i praznine oznacene u bojama s 0
        for($a = 0; $a < 6; $a++){
            for($b = 0 ; $b < 6; $b++){
                if($this->boje[$a][$b] === 1 || $this->boje[$a][$b]=== 0)
                    continue;
                else{
                    //gledamo taj broj na toj poziciji i odredjujemo boju
                    if($this->isValidNumber($this->igraca_ploca,$a,$b,$this->igraca_ploca[$a][$b]))
                        //ako vrati true
                        $this->boje[$a][$b] = 2;
                    else
                        $this->boje[$a][$b] = 3;
                }
            }
        }
        //provjeravamo podudara ima li razlike na zajednickim koordinatama u matrici boja i rjesenja, 
        //ako da vratimo da su razliciti, inace imamo rjesen sudoku
            for($a = 0; $a < 6; $a++){
                for($b = 0 ; $b < 6; $b++)  
                    if($this->boje[$a][$b] !== $this->rjesenje[$a][$b])
                        return RijesiSudoku::RAZLICITI_SU;
        }
        ++$this->brojPokusaja;
        return RijesiSudoku::PODUDARAJU_SE;
        }
    //gledamo iducu opciju, a to je ako brisemo broj
    else if($odabir === "obrisi_broj"){
        $l = $_POST['izbrisi_redak'] - 1;
        $k = $_POST['izbrisi_stupac'] - 1;
        if($this->ploca[$l][$k] !== '');
        else
        {
            $this -> igraca_ploca[$l][$k] = ''; //stavljamo ponovno prazninu - obrisali broj
            $this -> boje[$l][$k] = 0; // pripadna oznaka boje, to jest 0 ako nema broja, već je praznina na tim koordinatama
        }
        //gledamo brojeve koji su prethodno uneseni i odredjujemo boje
        for($a=0; $a<6;$a++){
            for($b=0;$b<6;$b++){
                if($this->boje[$a][$b] === 1 || $this->boje[$a][$b]=== 0)
                    continue;
                else{
                    //gledamo taj broj na toj poziciji i odredjujemo boju
                    if($this->isValidNumber($this->igraca_ploca,$a,$b,$this->igraca_ploca[$a][$b]))
                        //ako vrati true
                        $this->boje[$a][$b] = 2;
                    else
                        $this->boje[$a][$b] = 3;
                }

            }
        }
    }
    else if($odabir === "reset_sudoku"){
        $this->igraca_ploca = $this->ploca; //vracamo igracu plocu na pocetno stanje
        $this->boje = $this->pocetne_boje; //vracamo boje na pocetno stanje
        $this->brojPokusaja = -1;
    }
    }
    else{   //ako igrac nije odabrao nikakvu opciju
        return false;
        }
    }

    function ispisiCestitku(){
        ?>
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="utf-8">
			<title>Sudoku 6x6 - Bravo!</title>
            <link rel="stylesheet" href="sudoku.css" />
		</head>
		<body>
			<p>
				Bravooo, <?php echo htmlentities( $this->imeIgraca ); ?>! 
				<br />
				Riješio si sudoku u  
                <?php echo $this->brojPokusaja; ?> pokušaja!
			</p>
            <br/>
            <p>
                Tvoj riješeni sudoku izgleda ovako:
            </p>

            <table>
        <?php

        //ispisujemo riješeni sudoku na način da za početne brojeve ostavimo u crnoj boji, a dobro unešene u plavoj
        for($br_r = 0; $br_r <6 ; $br_r++){
            if($br_r === 1 || $br_r === 3)
                echo '<tr class="horizontalno">'; //ako je broj u 2. ili 4. retku podebljamo crte za odgovarajuci izgled 6x6 sudokua
            else
                echo '<tr>';
            for($br_s = 0; $br_s < 6; ++$br_s){
                if($this->boje[$br_r][$br_s] === 1)
                {
                    if($br_s === 2)
                        echo '<td class = "pocetno_vodoravno">' . $this->ploca[$br_r][$br_s] .'</td>'; //ako je broj u 3. stupcu, podebljamo liniju desno od njega
                    else
                        echo '<td class = "pocetno">' . $this->ploca[$br_r][$br_s] . '</td>';
                }
                else
                {
                    if($br_s === 2)
                        echo '<td class="dobro_vodoravno">'. $this->igraca_ploca[$br_r][$br_s] .'</td>'; //ako je broj u 3. stupcu, podebljamo liniju desno od njega
                    else
                        echo '<td class="dobro">' . $this->igraca_ploca[$br_r][$br_s] . '</td>';
                }
            }
            echo '</tr>';
        }
        ?>
    </table>

	</body>
	</html>

	<?php
    }
	function isGameOver() { return $this->gameOver; }
    function run() 
    {
        //Funkcija ce obavljati samo jedan potez u igri
        //Prvo, restetiraj poruke o greški.
        $this->errorMsg = false;

        //Prvo provjeri imamo li uopće igraca za naš sudoku
        if($this->get_imeIgraca() === false)
        {
            //Ako nemamo ime igrača, ispiši formu za unos imena i tako ćemo dobiti ime igrača
            $this->IspisiFormuZaIme();
            return;
        }
        //Dakle, imamo ime igrača
        //Ako je igrač pokušao izvršiti neku opciju, provjerimo što se dogodilo s tom opcijom
        $rez = $this->obradiPokusaj();
        if($rez === RijesiSudoku::PODUDARAJU_SE)
        {
            $this->ispisiCestitku();
            $this->gameOver = true;
        }
        else
            $this->ispisiFormuZaRjesavanjeSudoku($this->ploca);
    }
    
};

//-----GLAVNI DIO------
session_start();

if(!isset($_SESSION['igra']))
{
    // Ako igra još nije započela, stvori novi objekt tipa RijesiSudoku i spremi ga u $_SESSION
    $igra = new RijesiSudoku();
    $_SESSION['igra'] = $igra;
}

else
{
    // Ako je igra već ranije započela, dohvati ju iz $_SESSION-a	
    $igra = $_SESSION['igra'];
}

// Izvedi jedan korak u igri, u kojoj god fazi ona bila.
$igra->run();

if( $igra->isGameOver() )
{
    // Kraj igre -> prekini session.
    session_unset();
    session_destroy();
}

else
{
    // Igra još nije gotova -> spremi trenutno stanje u SESSION
    $_SESSION['igra'] = $igra;	
}
