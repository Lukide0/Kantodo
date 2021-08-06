import re
from os import chdir, rename, system, name, path, getcwd
from Console import Console

CLASS_NAME_REGEX = r"^[A-Z][a-zA-Z0-9_]*$"

PATH_CONTROLLER = "./../Pages/Controllers/"
PATH_VIEW = "./../Pages/Views/"
PATH_WIDGET = "./../Pages/Widgets/"
PATH_MODEL = "./../Pages/Models/"


# CLEAR
def clear_command(commands = []):
    system('cls' if name == 'nt' else 'clear')
#-----------------------------------------------------------
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
        

        with open("controller_template.txt", "r") as template:
            temp = template.read()
        
        temp = temp.replace("{{NAME}}", name, 1)

        name = f"{name}Controller.php"

        if path.isfile(PATH_CONTROLLER + name):
            Console.set_style(Console.RED_FG, content="Controller exits\n")
            return

        with open(PATH_CONTROLLER + name, "x") as file:
            file.write(temp)
        
        Console.set_style(Console.GREEN_FG, content="Controller was created\n")
    elif action == "-v":
        name = commands.pop(0)

        if re.match(CLASS_NAME_REGEX, name) is None:
            Console.set_style(Console.RED_FG, content="Invalid class name\n")
            return
        

        with open("view_template.txt", "r") as template:
            temp = template.read()
        
        temp = temp.replace("{{NAME}}", name, 1)

        name = f"{name}View.php"

        if path.isfile(PATH_VIEW + name):
            Console.set_style(Console.RED_FG, content="View exits\n")
            return

        with open(PATH_VIEW + name, "x") as file:
            file.write(temp)
        
        Console.set_style(Console.GREEN_FG, content="View was created\n")
    elif action == "-w":
        if len(commands) == 1:
            abstract = False
            name = commands.pop(0)
        elif len(commands) == 2 and commands[0] == '-a':
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

        with open(template_path, "r") as template:
            temp = template.read()
        
        temp = temp.replace("{{NAME}}", name, 1)

        name = f"{name}.php"

        if path.isfile(PATH_WIDGET + name):
            Console.set_style(Console.RED_FG, content="Widget exits\n")
            return

        with open(PATH_WIDGET + name, "x") as file:
            file.write(temp)
        Console.set_style(Console.GREEN_FG, content="Widget was created\n")
    elif action == "-m":
        if len(commands) == 1:
            table = False
            name = commands.pop(0)
        elif len(commands) == 3 and commands[0] == '-t':
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

        with open("model_template.txt", "r") as template:
            temp = template.read()
        
        temp = temp.replace("{{NAME}}", name, 1)
        temp = temp.replace("{{TABLE}}", table_line, 1)

        name = f"{name}Model.php"

        if path.isfile(PATH_MODEL + name):
            Console.set_style(Console.RED_FG, content="Model exits\n")
            return

        with open(PATH_MODEL + name, "x") as file:
            file.write(temp)
        
        Console.set_style(Console.GREEN_FG, content="Model was created\n")
    else:
        add_command_help()
        return

def add_command_help():
    print("""
add [options] NAME

    create file

    Options
        -c          create controller
        -v          create view
        -w [-a]     create widget
            -a          extend AbstractWidget
        -m [-t TABLE]   create model
            -t TABLE    set table
""")

#-----------------------------------------------------------
# RENAME
def rename_command(commands):
    if len(commands) < 3:
        rename_command_help()
        return

    action = commands.pop(0)
    name = commands.pop(0)
    new_name = commands.pop(0)

    if re.match(CLASS_NAME_REGEX, new_name) is None:
        Console.set_style(Console.RED_FG, content="Invalid new class name\n")
        return


    if action == "-c":
        folder = PATH_CONTROLLER
        name += "Controller"
        new_name += "Controller"
    elif action == "-v":
        folder = PATH_VIEW
        name += "View"
        new_name += "View"
    elif action == "-w":
        folder = PATH_WIDGET
    elif action == "-m":
        folder = PATH_MODEL
        name += "Model"
        new_name += "Model"
    else:
        rename_command_help()
        return
    
    if path.isfile(folder + name + ".php") == False:
        Console.set_style(Console.RED_FG, content=f"{name} doen't exits\n")
        return

    with open(folder + name + ".php") as file:
        content = file.read()

    content = content.replace(name, new_name)

    with open(folder + name + ".php", "w") as file:
        file.write(content) 

    rename(folder + name + ".php", folder + new_name + ".php")

    

    Console.set_style(Console.GREEN_FG, content=f"Renamed\n")
    

def rename_command_help():
    print("""
rename [options] NAME NEWNAME

    reaname file and class inside

    Options
        -c          rename controller
        -v          rename view
        -w          rename widget
        -m          rename model
""")
#-----------------------------------------------------------
# MIGRATION
def migration_command(commands):
    if len(commands) < 2:
        migration_command_help()
        return

    version = commands.pop(0)
    params = " ".join(commands)

    chdir("../")

    system('php -f Src/Core/Database/Migration/ConsoleRunner.php ' + version + " " + params)

    chdir("./Generators/")


def migration_command_help():
    print("""
mig VERSION [Options]

    migration

    Options
        -o          put changes in sql file
        -e          execute
        -n          don't load schema
        -c VERSION  set temp. current version in format X_X
""")
