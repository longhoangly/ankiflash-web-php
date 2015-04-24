104.131.145.250
root/1029qpwo

mysql> create database anki;
mysql> create table tblFlashcards
(
	flsPk int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	flsWord varchar(255) UNIQUE NOT NULL,
	flsWordType varchar(255),
	flsPhonetic varchar(255),
	flsExample TEXT,
	flsSound_uk varchar(255),
	flsSound_us varchar(255),
	flsThumb varchar(255) NOT NULL,
	flsImg varchar(255) NOT NULL,
	flsEntryContent MEDIUMTEXT NOT NULL,
	flsTag varchar(5) NOT NULL,
	flsCopyRight varchar(255) NOT NULL,
	flsAnkiDeck MEDIUMTEXT NOT NULL,
	flsCreatedTime TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	flsLastQueryTime TIMESTAMP NOT NULL
); 

mysql> drop table tblFlashcards;

mysql> insert into tblFlashcards (flsWord, flsWordType, flsPhonetic, flsExample, flsSound_uk, flsSound_us, flsThumb, flsImg, flsEntryContent, flsTag, flsCopyRight, flsAnkiDeck, flsLastQueryTime) values ($word, $wordType, $phonetic, $example, $sound_uks[0], $sound_us[0], $thumb, $img, $entryContent, $tag, $copyRight, $ankiDeck, NOW());

$sql = "INSERT INTO $table (flsWord, flsWordType, flsPhonetic, flsExample, flsSound_uk, flsSound_us, flsThumb, flsImg, flsEntryContent, flsTag, flsCopyRight, flsAnkiDeck, flsLastQueryTime) VALUES (\"$word\", \"$wordType\", \"$phonetic\", \"$example\", \"$sound_uks[0]\", \"$sound_us[0]\", \"$thumb\", \"$img\", \"$entryContent\", \"$tag\", \"$copyRight\", \"$ankiDeck\", NOW())";

NOTE:
Change mode 777 for parent folder
Install php5.5
install mysql5.6
delete alias for icons folder