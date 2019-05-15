<?php
/**
 * Created by PhpStorm.
 * User: Fow
 * Date: 16.04.2018
 * Time: 23:36
 */
namespace MainModule\handler\message;
class Intelligence
{
    public static function help()
    {
        return "Чтобы посмотреть справку, напиши \"помощь\"";
    }
    public static function themes()
    {
        return "Если хочешь узнать список тем, напиши \"темы\".
                Чтобы посмотреть список всех команд, которые я понимаю, напиши \"помощь\"";
    }
    public static function tasks()
    {
        return "Извини, я понимаю всего три похожие команды: 
                \"задание\" - я пришлю тебе случайно выбранное мной задание
                \"задание [тема]\" - я пришлю тебе задание по теме, которую ты укажешь
                \"задание [номер в КИМе]\" - я пришлю тебе задание по номеру из КИМа
                Чтобы посмотреть список всех команд, которые я понимаю, напиши \"помощь\"";
    }
    public static function anasysis()
    {
        return "Если ты хочешь получить разбор какого-либо задания, напиши мне \"разбор [номер задания]\"\r\n
                Чтобы посмотреть список всех команд, которые я понимаю, напиши \"помощь\"";
    }
    public static function resources()
    {
        return "Ты хочешь узнать список ресурсов? Напиши мне \"ресурсы\"\r\n
                Чтобы посмотреть список всех команд, которые я понимаю, напиши \"помощь\"";
    }
    public static function resource()
    {
        return "Чтобы установить ресурс, который тебе нравится, напиши \"ресурс [название ресурса]\"\r\n
                Чтобы посмотреть список всех команд, которые я понимаю, напиши \"помощь\"";
    }
    public static function answer()
    {
        return "Чтобы узнать ответ на задание, напиши мне \"ответ [номер задания]\"\r\n
                Чтобы посмотреть список всех команд, которые я понимаю, напиши \"помощь\"";
    }
    public static function check()
    {
        return "Если хочешь узнать, правильный ли у тебя ответ на задание, напиши мне [номер задания][ответ]\r\n
                Чтобы посмотреть список всех команд, которые я понимаю, напиши \"помощь\"";
    }
    public static function incompleted()
    {
        return "Чтобы посмотреть список нерешённых тобой заданий, напиши мне \"задания\"\r\n
                Чтобы посмотреть список всех команд, которые я понимаю, напиши \"помощь\"";
    }
    public static function year()
    {
        return "Чтобы установить год, с которого ты хочешь получать задания, напиши мне \"год [номер года]\"\r\n
                Чтобы посмотреть список всех команд, которые я понимаю, напиши \"помощь\"";
    }
    public static function repeat()
    {
        return "Если ты хочешь посмотреть снова на условие задания, которое было запрошено ранее, напиши мне \"условие [номер задания]\"\r\n
                Чтобы посмотреть список всех команд, которые я понимаю, напиши \"помощь\"";
    }
    public static function statistics()
    {
        return "Эту команду я не знаю, но зато я знаю 2 похожие:\r\n
                \"статистка\" - я пришлю тебе статистику по взятым тобой заданиям\r\n
                \"статистика [номер задания]\" - я пришлю тебе статистику по указанному тобой заданию\r\n
                Чтобы посмотреть список всех команд, которые я понимаю, напиши \"помощь\"";
    }
    public static  function  hello()
    {
        return "Привет :) Я бот-помощъник. Чтобы узнать, что я умею, напиши мне \"помощь\"";
    }
    public static function whatsup($hello=True)
    {
        $answers = array("У меня всё замечательно, я же бот :)",
            "Всё ок",
            "Сегодня я чувствую себя лучше чем обычно",
            "Прекрасно!",
            "Супер",
            "Гладил котика сегодня, так что всё великолепно",
            "Лучше не бывает!",
            "Я съел пиццу вчера, чувствую себя прекрасно");
        return $hello ? "Привет! " . $answers[rand(0, count($answers) - 1)] : $answers[rand(0, count($answers) - 1)];
    }
    public static function bot()
    {
        $answers = array("что?", "а?", "чего тебе? напиши \"помощь\"", "где?", "какой?", "это я", "это не я", "бот", "м?", "мяу", "мррр", "А");
        return $answers[rand(0, count($answers) - 1)];
    }
    public static function thanks()
    {
        $answers = array("Не за что","Всегда пожалуйста", "Рад был помочь", ":)");
        return $answers[rand(0, count($answers) - 1)];
    }
}