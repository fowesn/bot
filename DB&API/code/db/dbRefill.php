<?php
/**
 * Created by PhpStorm.
 * User: Anthony
 * Date: 17.05.2019
 * Time: 19:35
 */


define('PDF_NAME', 'pdf');
define('IMG_NAME', 'изображение');
// ссылки на получившиеся файлы буду содержать данный путь (для бота)
define('RESOURCES_URL', 'http://kappa.cs.petrsu.ru/~nestulov/API/v2/files/resources/');
// путь к директории, содержащей файлы
define('RESOURCES_DIR', '/home/02/nestulov/public_html/API/v2/files/resources/');

include "dbConnection.php";
include "/home/02/nestulov/public_html/API/v2/config/dbSettings.php";

function create_resource($resource_name)
{
    // получение .pdf и .dvi файлов с помощью pdflatex
    exec('latexmk -dvi -pdf -silent -jobname=' . $resource_name . ' -cd ' . RESOURCES_DIR . 'temp.tex', $output,$exit_code);
    if ($exit_code !== 0)
    {
        exit("latexmk compiling solution error!\n");
    }

    // получение .png файла из dvi с помощью dvipng
    exec('dvipng --strict -T tight -o ' . RESOURCES_DIR . $resource_name . '\(%d\).png ' . RESOURCES_DIR . $resource_name . '.dvi', $output,$exit_code);
    if ($exit_code !== 0)
    {
        exit("dvipng converting solution error!\n");
    }

    // удаление сопутствующих файлов (кроме .dvi)
    exec('latexmk -silent -jobname=' . $resource_name .  ' -cd ' . RESOURCES_DIR . 'temp.tex -c', $output,$exit_code);
    if ($exit_code !== 0)
    {
        exit("latexmk cleaning error!\n");
    }

    // удаление файла dvi
    exec('rm ' . RESOURCES_DIR . $resource_name . '.dvi', $output,$exit_code);
    if ($exit_code !== 0)
    {
        exit("rm error!\n");
    }
}



$conn = dbConnection::getConnection();

// находим все варианты заданий, для которых существуют неиспользованные входные данные
$query_variant = $conn->query('SELECT * FROM variant
                               WHERE variant.variant_id IN (
                                  SELECT input.variant_id FROM input
                                  WHERE input.used = 0
                                  GROUP BY input.variant_id
                                  HAVING COUNT(*) > 0)');
$query_variant->execute();

// для каждого подходящего варианта задания
while(($variant_tuple = $query_variant->fetch()) !== false)
{
    // идентификатор записи для будущих файлов с разборами
    $variant_id = $variant_tuple['variant_id'];

    // получаем записи из таблицы task (тема, номер в КИМе)
    $query = $conn->prepare('SELECT * FROM task
                             WHERE task.task_id = ?');
    $query->execute(array($variant_tuple['task_id']));
    $task_tuple = $query->fetch();

    /*
     * шаг 1 - получить внешние ключи на типы ресурсов
     */

    $query = $conn->prepare('SELECT resource_type.resource_type_id FROM resource_type 
                             WHERE resource_type.resource_type_code = ?');
    $query->execute(array(PDF_NAME));
    $PDF_resource_type = $query->fetch()['resource_type_id'];

    $query = $conn->prepare('SELECT resource_type.resource_type_id FROM resource_type 
                             WHERE resource_type.resource_type_code = ?');
    $query->execute(array(IMG_NAME));
    $IMG_resource_type = $query->fetch()['resource_type_id'];


    /*
     * шаг 2 - проверить существование файлов с разбором
     * (если задания с данным условием добавлялись ранее, то файлы, ресурсы и коллекция ресурсов для разборов уже существуют)
     */

    echo ("Verifying solution resources existence...\n");

    $query = $conn->prepare('SELECT resource_collection
                             FROM solution_resource_collection
                             WHERE solution_resource_collection.variant = ?');
    $query->execute(array($variant_id));
    // коллекция ресурсов для разбора не найдена
    if (($solution_resource_collection = $query->fetch()) === false)
    {
        echo('Solution files for variant ' . $variant_id . " were NOT found!\n");

        $solution_exists = false;

        // файл .tex с разбором
        if (($solution_file = fopen(RESOURCES_DIR . 'temp.tex', "w")) === false)
        {
            exit("Opening temp.tex error!\n");
        }

        // разбор
        if (fprintf($solution_file, file_get_contents($variant_tuple['solution'])) === 0)
        {
            exit("Writing to temp.tex error!\n");
        }

        create_resource($variant_id);

        echo('Solution files for variant ' . $variant_id . " were successfully created!\n");
    }
    else
    {
        $solution_resource_collection = $solution_resource_collection['resource_collection'];

        echo('Solution files for variant ' . $variant_id . " were found!\n");

        $solution_exists = true;
    }

    /*
     * шаг 3 - для каждого набора данных добавить ресурсы и сформировать задания
     */
    $query_input = $conn->prepare('SELECT * FROM input
                                   WHERE input.used = 0
                                   AND input.variant_id = ?');
    $query_input->execute(array($variant_id));

    echo('Starting input processing for variant ' . $variant_id . "...\n");

    while(($input_tuple = $query_input->fetch()) !== false)
    {
        /*
         * шаг 3.1 - подготовка файлов условия
         */

        // строка с входными данными, разделенными пробелами
        $input = $input_tuple['input'];

        // массив входных данных
        $input = explode(' ', $input);

        // часть имен будущих файлов
        $input_id = $input_tuple['input_id'];

        // файл .tex с условием
        if (($statement_file = fopen(RESOURCES_DIR . 'temp.tex', "w")) === false)
        {
            exit("Opening temp.tex error!\n");
        }

        // конкретное задание, получаемое набивкой условия входными данными
        if (vfprintf($statement_file, file_get_contents($variant_tuple['statement']), $input) === 0)
        {
            exit("Writing to temp.tex error!\n");
        }

        create_resource($variant_id . '-' . $input_id);

        echo('Statement files for input ' . $input_id . " were successfully created!\n");

        /*
         * шаг 3.2 - пополнить БД новыми ресурсами
         */

        // на данном этапе в БД будет добавлено несколько кортежей в разные таблицы
        // для того чтобы в случае ошибки избежать добавления данных, команды выполняются как транзакция
        try
        {
            $conn->beginTransaction();

            // если разбор еще не был добавлен в БД
            if (!$solution_exists)
            {
                $query = $conn->query('INSERT INTO resource_collection VALUES ()');
                $solution_resource_collection = $conn->lastInsertId();

                // добавление pdf-файла разбора
                $query = $conn->prepare('INSERT INTO resource (resource_collection_id, resource_name, resource_content, resource_type_id)
                                         VALUES (?, ?, ?, ?)');
                $query->execute(array(
                    $solution_resource_collection,
                    $variant_id . '.pdf',
                    RESOURCES_URL . $variant_id . '.pdf',
                    $PDF_resource_type,
                ));

                // утилита dvipng создаст столько изображений, сколько страниц в pdf-файле
                // если изображение найдено, нужно его добавить в коллекцию
                $page = 1;
                while (file_exists(RESOURCES_DIR . $variant_id . '(' . $page . ').png'))
                {
                    $query = $conn->prepare('INSERT INTO resource (resource_collection_id, resource_name, resource_content, resource_type_id)
                                             VALUES (?, ?, ?, ?)');
                    $query->execute(array(
                        $solution_resource_collection,
                        $variant_id . '(' . $page . ').png',
                        RESOURCES_URL . $variant_id . '(' . $page . ').png',
                        $IMG_resource_type,
                    ));
                    $page++;
                }

                echo('solution resource collection ' . $solution_resource_collection . " was successfully created!\n");
                echo($page . " resources were successfully added to the database!\n");

                // сохранить коллекцию ресурсов, содержащую ресурсы разбора для данного варианта задания
                $query = $conn->prepare('INSERT INTO solution_resource_collection (variant, resource_collection)
                                         VALUES (?, ?)');
                $query->execute(array($variant_id, $solution_resource_collection));

                $solution_exists = true;
            }

            // создание новой коллекции ресурсов для условия
            $query = $conn->query('INSERT INTO resource_collection VALUES ()');
            $statement_resource_collection = $conn->lastInsertId();

            // добавление pdf-файла условия
            $query = $conn->prepare('INSERT INTO resource (resource_collection_id, resource_name, resource_content, resource_type_id)
                                     VALUES (?, ?, ?, ?)');
            $query->execute(array(
                $statement_resource_collection,
                $variant_id . '-' . $input_id . '.pdf',
                RESOURCES_URL . $variant_id . '-' . $input_id . '.pdf',
                $PDF_resource_type,
            ));

            // утилита dvipng создаст столько изображений, сколько страниц в pdf-файле
            // если изображение найдено, нужно его добавить в коллекцию
            $page = 1;
            while (file_exists(RESOURCES_DIR . $variant_id . '-' . $input_id . '(' . $page . ').png'))
            {
                $query = $conn->prepare('INSERT INTO resource (resource_collection_id, resource_name, resource_content, resource_type_id)
                                             VALUES (?, ?, ?, ?)');
                $query->execute(array(
                    $statement_resource_collection,
                    $variant_id . '-' . $input_id . '(' . $page . ').png',
                    RESOURCES_URL . $variant_id . '-' . $input_id . '(' . $page . ').png',
                    $IMG_resource_type,
                ));
                $page++;
            }

            echo('statement resource collection ' . $statement_resource_collection . " was successfully created!\n");
            echo($page . " resources were successfully added to the database!\n");

            /*
             * шаг 3.3 - добавить новое задание
             */

            $query = $conn->prepare('INSERT INTO problem (problem_statement, problem_answer, problem_solution, problem_created, problem_modified, problem_type_id, exam_item_id, problem_year)
                                VALUES (?, ?, ?, NOW(), NOW(), ?, ?, ?)');
            $query->execute(array(
                $statement_resource_collection,
                $input_tuple['answer'],
                $solution_resource_collection,
                $task_tuple['problem_type_id'],
                $task_tuple['exam_item_id'],
                $variant_tuple['variant_year']
            ));

            echo('problem ' . $conn->lastInsertId() .  " created\n");

            /*
             * шаг 3.4 - обновить кортеж с входными данными
             */

            $query = $conn->prepare('UPDATE input SET input.used = 1
                                 WHERE input.input_id = ?');
            $query->execute(array($input_tuple['input_id']));

            echo('input ' . $input_tuple['input_id'] . " marked used\n");

            $conn->commit();

        }
        catch (Exception $e)
        {
            $conn->rollback();
            throw $e;
        }
    }

    echo ('Processing variant ' . $variant_id . " is done!\n");
}

$conn = null;

echo ("All is done, congratulations!\n");