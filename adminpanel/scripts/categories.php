<?php

require_once "../../engine/main.php";
\Engine\Engine::LoadEngine();

if ($sessionRes = \Users\UserAgent::SessionContinue()) $user = new \Users\User($_SESSION["uid"]);
else { header("Location: ../../adminpanel.php?p=forbidden"); exit; }

if (isset($_POST["category-add-btn"])){
    if ($user->UserGroup()->getPermission("category_create")){
        if (empty($_POST["category-add-name"])){
            header("Location: ../../adminpanel.php?p=categories&reqtype=1&res=6ncn");
            exit;
        }
        if (empty($_POST["category-add-description"])){
            header("Location: ../../adminpanel.php?p=categories&reqtype=1&res=6ncd");
            exit;
        }
        if (strlen($_POST["category-add-name"]) < 4 || strlen($_POST["category-add-name"]) > 50){
            header("Location: ../../adminpanel.php?p=categories&reqtype=1&res=6nvcn");
            exit;
        }
        if (strlen($_POST["category-add-description"]) < 4 || strlen($_POST["category-add-description"]) > 350){
            header("Location: ../../adminpanel.php?p=categories&reqtype=1&res=6nvcd");
            exit;
        }

        $result = \Forum\ForumAgent::CreateCategory($_POST["category-add-name"], $_POST["category-add-description"],
            (isset($_POST["category_add_public"])) ? 1 : 0,
            (isset($_POST["category_add_nocomments"])) ? 1 : 0,
            (isset($_POST["category_add_notopics"])) ? 1 : 0);
        if ($result === TRUE){
            \Guards\Logger::LogAction($user->getId(), "создал(а) новую категорию " . $_POST["category-add-name"]);
            header("Location: ../../adminpanel.php?p=categories&res=6scc");
            exit;
        } else {
            header("Location: ../../adminpanel.php?p=categories&res=6ncc");
            exit;
        }
    } else {
        header("Location: ../../adminpanel.php?p=categories&res=1");
        exit;
    }
}

if (isset($_POST["category_edit_btn"])){
    if ($user->UserGroup()->getPermission("category_edit")){
        if (!empty($_POST["cid"])) {
            header("Location: ../../adminpanel.php?p=categories&reqtype=2&cid=" . $_POST["cid"]);
            exit;
        } else {
            header("Location: ../../adminpanel.php?p=categories&res=6ncid");
            exit;
        }
    } else {
        header("Location: ../../adminpanel.php?p=categories&res=1");
        exit;
    }
}

if (isset($_POST["category_edit_save"])){
    if ($user->UserGroup()->getPermission("category_edit")){
        if (empty($_POST["cid"])){
            header("Location: ../../adminpanel.php?p=categories&res=6ncid");
            exit;
        }

        $category = new \Forum\Category($_POST["cid"]);
        if ($category == 32){
            header("Location: ../../adminpanel.php?p=categories&res=6nct");
            exit;
        }

        if (empty($_POST["category_edit_name"])){
            header("Location: ../../adminpanel.php?p=categories&reqtype=2&res=6ncn&cid=" . $category->getId());
            exit;
        }
        if (empty($_POST["category_edit_descript"])){
            header("Location: ../../adminpanel.php?p=categories&reqtype=2&res=6ncd&cid=" . $category->getId());
            exit;
        }
        if (strlen($_POST["category_edit_name"]) < 4 || strlen($_POST["category_edit_name"]) > 50 ){
            header("Location: ../../adminpanel.php?p=categories&reqtype=2&res=6nvcn&cid=" . $category->getId());
            exit;
        }
        if (strlen($_POST["category_edit_descript"]) < 4 || strlen($_POST["category_edit_descript"]) > 350 ){
            header("Location: ../../adminpanel.php?p=categories&reqtype=2&res=6nvcd&cid=" . $category->getId());
            exit;
        }

        if ($category->getName() != $_POST["category-edit-name"]) {
            \Guards\Logger::LogAction($user->getId(), "переименовала(а) категорию " . $category->getName() . " -> " . $_POST["category-edit-name"]);
            \Forum\ForumAgent::ChangeCategoryParams($_POST["cid"], "name", $_POST["category_edit_name"]);
        }
        if ($category->getDescription() != $_POST["category-edit-descript"]) {
            \Guards\Logger::LogAction($user->getId(), "изменил(а) описание категории " . $category->getName() . " " . $category->getDescription() . " -> " . $_POST["category-edit-descript"]);
            \Forum\ForumAgent::ChangeCategoryParams($_POST["cid"], "descript", $_POST["category_edit_descript"]);
        }
        if ($category->isPublic() != $_POST["category_edit_public_checker"]) {
            \Guards\Logger::LogAction($user->getId(), "изменил(а) публичность категории " . $category->getName() . " " . $category->isPublic() . " -> " . $_POST["category_edit_public_checker"]);
            \Forum\ForumAgent::ChangeCategoryParams($_POST["cid"], "public", (isset($_POST["category_edit_public_checker"])) ? "1" : "0");
        }
        if ($category->CanCreateComments() != $_POST["category_edit_nocomments_checker"]) {
            \Guards\Logger::LogAction($user->getId(), "изменил(а) право на создание комментариев в категории " . $category->getName() . " "
                . $category->CanCreateComments() . " -> " . $_POST["category_edit_nocomments_checker"]);
            \Forum\ForumAgent::ChangeCategoryParams($_POST["cid"], "no_comment", (isset($_POST["category_edit_nocomments_checker"])) ? "1" : "0");
        }
        if ($category->CanCreateTopic() != $_POST["category_edit_notopics_checker"]) {
            \Guards\Logger::LogAction($user->getId(), "изменил(а) право на создание комментариев в категории " . $category->getName() . " "
                . $category->CanCreateTopic() . " -> " . $_POST["category_edit_notopics_checker"]);
            \Forum\ForumAgent::ChangeCategoryParams($_POST["cid"], "no_new_topics", (isset($_POST["category_edit_notopics_checker"])) ? "1" : "0");
        }

        header("Location: ../../adminpanel.php?p=categories&res=6sce");
        exit;
    } else {
        header("Location: ../../adminpanel.php?p=categories&res=1");
        exit;
    }
}

if (isset($_POST["category_edit_delete"])){
    if ($user->UserGroup()->getPermission("category_delete")){
        if (empty($_POST["cid"])){
            header("Location: ../../adminpanel.php?p=categories&res=6ncid");
            exit;
        }
        $categoryName = \Forum\ForumAgent::GetCategoryParam($_POST["cid"], "name");
        $result = \Forum\ForumAgent::DeleteCategory($_POST["cid"]);
        if ($result === TRUE){
            \Guards\Logger::LogAction($user->getId(), "удалил(а) категорию $categoryName.");
            header("Location: ../../adminpanel.php?p=categories&res=6scdt");
            exit;
        }
        elseif ($result == 32) {
            if (empty($_POST["cid"])){
                header("Location: ../../adminpanel.php?p=categories&res=6ntc");
                exit;
            }
        } else {
            if (empty($_POST["cid"])){
                header("Location: ../../adminpanel.php?p=categories&res=6ncdt&reqtype=2&cid=" . $_POST["cid"]);
                exit;
            }
        }

    } else {
        header("Location: ../../adminpanel.php?p=categories&res=1");
        exit;
    }
}

if (isset($_POST["categories-table-delete"])){
    if ($user->UserGroup()->getPermission("category_delete")){
        if (empty($_POST["cid"])){
            header("Location: ../../adminpanel.php?p=categories&res=6ncid");
            exit;
        }

        $cids = explode(",", $_POST["cid"]);
        for ($y = 0; $y <= count($cids)-1; $y++){
            $categoryName = \Forum\ForumAgent::GetCategoryParam($_POST["cid"], "name");
            $result = \Forum\ForumAgent::DeleteCategory($_POST["cid"]);
            if ($result === TRUE){
                \Guards\Logger::LogAction($user->getId(), "удалил(а) категорию $categoryName.");
                continue;
            }
            elseif ($result == 32) {
                if (empty($_POST["cid"])){
                    header("Location: ../../adminpanel.php?p=categories&res=6ntc");
                    exit;
                }
            } else {
                if (empty($_POST["cid"])){
                    header("Location: ../../adminpanel.php?p=categories&res=6ncdt&reqtype=2&cid=" . $_POST["cid"]);
                    exit;
                }
            }
        }
        header("Location: ../../adminpanel.php?p=categories&res=6scdt");
        exit;
    } else {
        header("Location: ../../adminpanel.php?p=categories&res=1");
        exit;
    }
}

header("Location: ../../adminpanel.php?p=forbidden");
exit;