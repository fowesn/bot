<?php
/**
 * Created by PhpStorm.
 * User: Anthony
 * Date: 17.05.2019
 * Time: 19:35
 */

define('PDF_NAME', 'pdf');
define('IMG_NAME', 'изображение');
define('RESOURCES_PATH', 'http://kappa.cs.petrsu.ru/~nestulov/API/v2/files/resources/');

include "dbConnection.php";

$conn = dbConnection::getConnection();

$query_input = $conn->query('SELECT * FROM input
                       WHERE input.used = 0');
$query_input->execute();

while($input_tuple = $query_input->fetch())
{
    /*
     * шаг 1 - подготовить изображение и pdf-файл для условия и разбора (всего 4 файла)
     */

    // получение записи из таблицы variant (условие, разбор, год)
    $query = $conn->prepare('SELECT * FROM variant
                                  WHERE variant.variant_id = ?');
    $query->execute(array($input_tuple['variant_id']));
    $variant_tuple = $query->fetch();

    // получение записи из таблицы task (тема, номер в КИМе)
    $query = $conn->prepare('SELECT * FROM task
                                  WHERE task.task_id = ?');
    $query = $conn->execute(array($variant_tuple['task_id']));
    $task_tuple = $query->fetch();

    // строка с входными данными, разделенными пробелами
    $input = $query_input['input'];

    // массив входных данных
    $input = explode(' ', $input);

    // часть имен будущих файлов
    $name = $input_tuple['input_id'];

    // конкретное задание, получаемое набивкой условия входными данными
    $statement = vprintf(file_get_contents($variant_tuple['template']), $input);
    // файл .tex с условием
    $statement_file = fopen('../../files/resources/temp_p.tex', "w");
    // получение .pdf и .dvi файлов с помощью pdflatex
    exec('latexmk -dvi -pdf -silent -jobname=' . $name . 'P temp_p.tex');
    // получение .png файла из dvi с помощью dvipng
    exec('dvipng -T tight -o ' . $name . 'P.png ' . $name . 'P.dvi');
    // удаление сопутствующих файлов (кроме .dvi)
    exec('latexmk -jobname=' . $name . 'P temp_p.tex -c');
    // удаление файла dvi
    exec('rm ' . $name . 'P.dvi');

    // разбор
    $solution = file_get_contents($variant_tuple['solution']);
    // файл .tex с разбором
    $solution_file = fopen('../../files/resources/temp_s.tex', "w");
    // получение .pdf и .dvi файлов с помощью pdflatex
    exec('latexmk -dvi -pdf -silent -jobname=' . $name . 'S temp_s.tex');
    // получение .png файла из dvi с помощью dvipng
    exec('dvipng -T tight -o ' . $name . 'S.png ' . $name . 'S.dvi');
    // удаление сопутствующих файлов (кроме .dvi)
    exec('latexmk -jobname=' . $name . 'S temp_s.tex -c');
    // удаление файла dvi
    exec('rm ' . $name . 'S.dvi');


    /*
     * шаг 2 - пополнить базу данных новыми ресурсами
     */

    // создание новой коллекции ресурсов для условия
    $query = $conn->query('INSERT INTO resource_collection VALUES ()');
    $statement_resource_collection = $conn->lastInsertId();

    // получение внешних ключей на типы ресурсов
    $query = $conn->prepare('SELECT resource_type.resource_type_id FROM resource_type 
                                  WHERE resource_type.resource_type_code = ?');
    $query->execute(array(PDF_NAME));
    $PDF_resource_type = $query->fetch()['resource_type_id'];

    $query = $conn->prepare('SELECT resource_type.resource_type_id FROM resource_type 
                                  WHERE resource_type.resource_type_code = ?');
    $query->execute(array(IMG_NAME));
    $IMG_resource_type = $query->fetch()['resource_type_id'];

    // добавление ресурсов условий
    $query = $conn->prepare('INSERT INTO resource (resource_collection_id, resource_name, resource_content, resource_type_id)
                                  VALUES (?, ?, ?, ?), (?, ?, ?, ?)');
    $query->execute(array(
        $statement_resource_collection, $name . 'P.pdf', RESOURCES_PATH . $name . 'P.pdf', $PDF_resource_type,
        $statement_resource_collection, $name . 'P.png', RESOURCES_PATH . $name . 'P.png', $IMG_resource_type
    ));



    // создание новой коллекции ресурсов для разбора
    $query = $conn->query('INSERT INTO resource_collection VALUES ()');
    $solution_resource_collection = $conn->lastInsertId();

    // добавление ресурсов разборов
    $query = $conn->prepare('INSERT INTO resource (resource_collection_id, resource_name, resource_content, resource_type_id)
                                  VALUES (?, ?, ?, ?), (?, ?, ?, ?)');
    $query->execute(array(
        $solution_resource_collection, $name . 'S.pdf', RESOURCES_PATH . $name . 'S.pdf', $PDF_resource_type,
        $solution_resource_collection, $name . 'S.png', RESOURCES_PATH . $name . 'S.png', $IMG_resource_type
    ));

    /*
     * шаг 3 - добавить новое задание
     */

    $query = $conn->prepare('INSERT INTO problem (problem_statement, problem_answer, problem_solution, problem_created, problem_modified, problem_type_id, exam_item_id, problem_year)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $query->execute(array(
        $statement_resource_collection,
        $input_tuple['answer'],
        $solution_resource_collection,
        NOW(),
        NOW(),
        $task_tuple['problem_type_id'],
        $task_tuple['exam_item_id'],
        $variant_tuple['year']
    ));

    /*
     * шаг 4 - обновить кортеж с входными данными
     */
    $query = $conn->prepare('UPDATE input SET input.used = 1
                             WHERE input.input_id = ?');
    $query->execute(array($input_tuple['input_id']));
}

$conn = null;