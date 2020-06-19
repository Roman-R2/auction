<?php

declare(strict_types=1);

//Напишите серийный инсерт в две таблицы с возможностью отката
// при возникновении ошибки. Наименование переменных произвольное, используем PDO.



public function insertQuery(string $queryString, array $data)
{
    $stmt = self::$dbh->prepare($queryString);
    try {
        self::$dbh->beginTransaction();
        $stmt->execute($data);
        self::$dbh->commit();
    } catch (\Exception $e) {
        self::$dbh->rollback();
        print "Error!: " . $e->getMessage() . "<br/>";
    }
}
