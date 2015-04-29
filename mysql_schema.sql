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
1. Change mode 777 for parent folder
2. Install php5.5
3. Install mysql5.6 [Optional - NOW() function in mysql]
4. Delete alias for /icons folder

5. Change mysql config
-----------------------
[mysqld]
query_cache_size=32M
max_allowed_packet=32M
-----------------------
6. Install Zip package: yum install zip

7. Change php config [optional - we can use linux command to zip result]
-----------------------
realpath_cache_size = 16k
realpath_cache_ttl = 120
memory_limit = 1024M
-----------------------