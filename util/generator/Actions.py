from genericpath import isfile
import re
import json
import sys
from os import chdir, rename, system, name, path, getcwd
from Console import Console

CLASS_NAME_REGEX = r"^[A-Z][a-zA-Z0-9_]*$"

PATH_ABS = path.dirname(__file__)

PATH_CONTROLLER = path.join(PATH_ABS, "./../../pages/Controllers/")
PATH_VIEW = path.join(PATH_ABS, "./../../pages/Views/")
PATH_WIDGET = path.join(PATH_ABS, "./../../pages/Widgets/")
PATH_MODEL = path.join(PATH_ABS, "./../../pages/Models/")


# CLEAR
def clear_command(commands=[]):
    system("cls" if name == "nt" else "clear")


# -----------------------------------------------------------
# ADD
def add_command(commands):
    if len(commands) < 2:
        add_command_help()
        return

    action = commands.pop(0)

    if action == "-c":
        name = commands.pop(0)

        if re.match(CLASS_NAME_REGEX, name) is None:

            Console.set_style(Console.RED_FG, content="Invalid class name\n")
            return

        with open(path.join(sys.path[0], "controller_template.txt"), "r") as template:
            temp = template.read()

        temp = temp.replace("{{NAME}}", name, 1)

        name = f"{name}Controller.php"

        if path.isfile(path.join(sys.path[0], PATH_CONTROLLER + name)):
            Console.set_style(Console.RED_FG, content="Controller exits\n")
            return

        with open(path.join(sys.path[0], PATH_CONTROLLER + name), "x") as file:
            file.write(temp)

        Console.set_style(Console.GREEN_FG, content="Controller was created\n")
    elif action == "-v":
        name = commands.pop(0)

        if re.match(CLASS_NAME_REGEX, name) is None:
            Console.set_style(Console.RED_FG, content="Invalid class name\n")
            return

        with open(path.join(sys.path[0], "view_template.txt"), "r") as template:
            temp = template.read()

        temp = temp.replace("{{NAME}}", name, 1)

        name = f"{name}View.php"

        if path.isfile(path.join(sys.path[0], PATH_VIEW + name)):
            Console.set_style(Console.RED_FG, content="View exits\n")
            return

        with open(path.join(sys.path[0], PATH_VIEW + name), "x") as file:
            file.write(temp)

        Console.set_style(Console.GREEN_FG, content="View was created\n")
    elif action == "-w":
        if len(commands) == 1:
            abstract = False
            name = commands.pop(0)
        elif len(commands) == 2 and commands[0] == "-a":
            abstract = True
            name = commands.pop(1)
        else:
            add_command_help()
            return

        if re.match(CLASS_NAME_REGEX, name) is None:
            Console.set_style(Console.RED_FG, content="Invalid class name\n")
            return

        if abstract:
            template_path = "widget_abstract_template.txt"
        else:
            template_path = "widget_template.txt"

        with open(path.join(sys.path[0], template_path), "r") as template:
            temp = template.read()

        temp = temp.replace("{{NAME}}", name, 1)

        name = f"{name}.php"

        if path.isfile(path.join(sys.path[0], PATH_WIDGET + name)):
            Console.set_style(Console.RED_FG, content="Widget exits\n")
            return

        with open(path.join(sys.path[0], PATH_WIDGET + name), "x") as file:
            file.write(temp)
        Console.set_style(Console.GREEN_FG, content="Widget was created\n")
    elif action == "-m":
        if len(commands) == 1:
            table = False
            name = commands.pop(0)
        elif len(commands) == 3 and commands[0] == "-t":
            table = True
            table_name = commands.pop(1)
            name = commands.pop(1)
        else:
            add_command_help()
            return

        if re.match(CLASS_NAME_REGEX, name) is None:
            Console.set_style(Console.RED_FG, content="Invalid class name\n")
            return

        if table:
            table_line = f"$this->table = Connection::formatTableName('{table_name}');"
        else:
            table_line = ""

        with open(path.join(sys.path[0], "model_template.txt"), "r") as template:
            temp = template.read()

        temp = temp.replace("{{NAME}}", name, 1)
        temp = temp.replace("{{TABLE}}", table_line, 1)

        name = f"{name}Model.php"

        if path.isfile(path.join(sys.path[0], PATH_MODEL + name)):
            Console.set_style(Console.RED_FG, content="Model exits\n")
            return

        with open(path.join(sys.path[0], PATH_MODEL + name), "x") as file:
            file.write(temp)

        Console.set_style(Console.GREEN_FG, content="Model was created\n")
    else:
        add_command_help()
        return


def add_command_help():
    print(
        """
add [options] NAME

    create file

    Options
        -c          create controller
        -v          create view
        -w [-a]     create widget
            -a          extend AbstractWidget
        -m [-t TABLE]   create model
            -t TABLE    set table
"""
    )
# -----------------------------------------------------------
# MIGRATION
def migration_command(commands):
    if len(commands) < 2:
        migration_command_help()
        return

    version = commands.pop(0)
    params = " ".join(commands)

    chdir(path.join(PATH_ABS, "./../../"))

    system("php -f CLI/DatabaseMigration.php " + version + " " + params)

    chdir(PATH_ABS)


def migration_command_help():
    print(
        """
mig VERSION [Options]

    migration

    Options
        -o          put changes in sql file
        -e          execute
        -n          don't load schema
        -c VERSION  set temp. current version in format X_X
"""
    )


# -----------------------------------------------------------
# GENERATE ROUTES


def backup_command(commands):
    chdir(path.join(PATH_ABS, "./../../"))

    system("php -f CLI/AppBackup.php")

    chdir(PATH_ABS)


# -----------------------------------------------------------
# GENERATE ROUTES
def routes_command(commands):
    chdir(path.join(PATH_ABS, "./../../pages/"))

    Console.set_style(Console.YELLOW_FG, content="Searching for 'routes.json'\n")
    if path.isfile("./routes.json") == False:
        Console.set_style(Console.RED_FG, content="'routes.json' was not found\n")
        return
    Console.set_style(Console.BLUE_FG, content="'routes.json' was found\n")

    routesJSON = None
    with open("./routes.json") as file:
        routesJSON = json.load(file)

    index = 1

    paths = {"POST": [], "GET": []}

    php_code = "<?php\n// Auto generated by Console.py\n\nuse Kantodo\\Core\\Application;\nuse Kantodo\\Core\\Request;\n\n\n"

    for route in routesJSON:
        if check_route(route, paths, index) == False:
            chdir(PATH_ABS)
            return
        paths[route["method"]].append(route["path"])

        status = system(
            'php -r "include \\"./../loader/autoload.php\\";if (!class_exists(Kantodo\\Controllers\\{0}::class))exit(1);"'.format(
                route["controller"]["class"]
            )
        )
        if status != 0:
            Console.set_style(
                Console.RED_FG,
                content="Controller class '{}' does not exist\n".format(
                    route["controller"]["class"]
                ),
            )
            chdir(PATH_ABS)
            return

        status = system(
            'php -r "include \\"./../loader/autoload.php\\";if (!method_exists(Kantodo\\Controllers\\{0}::class, \\"{1}\\"))exit(1);"'.format(
                route["controller"]["class"], route["controller"]["method"]
            )
        )
        if status != 0:
            Console.set_style(
                Console.RED_FG,
                content="Controller class '{}' does not have method '{}'\n".format(
                    route["controller"]["class"], route["controller"]["method"]
                ),
            )
            chdir(PATH_ABS)
            return

        route_method = "Request::METHOD_{0}".format(route["method"])
        route_path = "'{}'".format(route["path"])
        route_callback = "[Kantodo\\Controllers\\{0}::class,'{1}']".format(
            route["controller"]["class"], route["controller"]["method"]
        )
        route_access = "Application::{0}".format(route["access"]["role"])
        route_strict = str(route["access"]["strict"]).lower()

        php_code += (
            "Application::$APP->router->addRoute({0}, {1}, {2}, {3}, {4});\n".format(
                route_method, route_path, route_callback, route_access, route_strict
            )
        )
        index += 1

    with open("routes.php", "w") as f:
        f.write(php_code)

    Console.set_style(Console.GREEN_FG, content="File 'routes.php' was generated\n")

    chdir(PATH_ABS)


def check_route(route, paths, index):
    mustContain = ["method", "path", "controller", "access"]
    roles = ["USER", "GUEST", "ADMIN"]
    for key in mustContain:
        if (key in route) == False:
            Console.set_style(
                Console.RED_FG,
                content="Route at index '{}' must contain '{}'\n".format(index, key),
            )
            return False

        if key == "controller":
            if ("class" in route["controller"]) == False:
                Console.set_style(
                    Console.RED_FG,
                    content="Route at index '{}' must contain 'controller' with 'class'\n".format(
                        index
                    ),
                )
                return False
            if ("method" in route["controller"]) == False:
                Console.set_style(
                    Console.RED_FG,
                    content="Route at index '{}' must contain 'controller' with 'method'\n".format(
                        index
                    ),
                )
                return False
        elif key == "access":
            if ("role" in route["access"]) == False or (
                route["access"]["role"] in roles
            ) == False:
                Console.set_style(
                    Console.RED_FG,
                    content="Route at index '{}' must contain 'access' with 'roles' ['USER', 'GUEST', 'ADMIN']\n".format(
                        index
                    ),
                )
                return False
            if ("strict" in route["access"]) == False or isinstance(
                route["access"]["strict"], bool
            ) == False:
                Console.set_style(
                    Console.RED_FG,
                    content="Route at index '{}' must contain 'access' with 'strict' (boolean)\n".format(
                        index
                    ),
                )
                return False
        filePath = "Controllers/{}.php".format(route["controller"]["class"])
        if path.isfile(filePath) == False:
            Console.set_style(
                Console.RED_FG,
                content="Controller '{}' does not exist\n".format(filePath),
            )
            return False

        if (route["method"] in paths) == False:
            Console.set_style(
                Console.RED_FG,
                content="Route at index '{}' has method '{}' which is not supported\n".format(
                    index, route["method"]
                ),
            )
            return False
        if route["path"] in paths[route["method"]]:
            Console.set_style(
                Console.RED_FG, content="Duplicit path '{}'\n".format(route["path"])
            )
            return False
    return True
