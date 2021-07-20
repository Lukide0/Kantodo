from os import rename, system, name, path
import re


from Commands import Commands

from Actions import *



class Console:
    RESET =  "\033[0m"
    BLACK_BG =  40
    RED_BG =  41
    GREEN_BG =  42
    YELLOW_BG =  43
    BLUE_BG =  44
    MAGENTA_BG =  45
    CYAN_BG =  46
    WHITE_BG =  47
    BLACK_FG =  30
    RED_FG =  31
    GREEN_FG =  32
    YELLOW_FG =  33
    BLUE_FG =  34
    MAGENTA_FG =  35
    CYAN_FG =  36
    WHITE_FG =  37


    def __init__(self):
    
        text = f"""
  $$    $$                        $$                      $$
  $$   $$                         $$                      $$
  $$  $$     $$$$$$   $$$$$$$   $$$$$$     $$$$$$    $$$$$$$   $$$$$$
  $$$$$           $$  $$    $$    $$      $$    $$  $$    $$  $$    $$
  $$  $$     $$$$$$$  $$    $$    $$      $$    $$  $$    $$  $$    $$
  $$   $$   $$    $$  $$    $$    $$  $$  $$    $$  $$    $$  $$    $$
  $$    $$   $$$$$$$  $$    $$     $$$$    $$$$$$    $$$$$$$   $$$$$$



"""
        self.set_style(self.YELLOW_FG, content=text)
        self.reset_style()
        self.commands = Commands()
        
        self.commands.register_command("add", add_command)
        self.commands.register_command("rename", rename_command)
        self.commands.register_command("clear", clear_command)


    @classmethod
    def set_style(cls, fg = None, bg = None, content = "", reset = True):
        style_str = "\033["

        opt = []

        if bg != None:
            opt.append(str(bg))
        if fg != None:
            opt.append(str(fg))

        style_str = "\033[{}m".format(";".join(opt))
        print(style_str + content, end="")

        if reset:
            cls.reset_style()

    @classmethod
    def reset_style(cls):
        print(cls.RESET, end="")


    def run(self):

        while True:
            self.set_style(self.YELLOW_FG, content="> ")
            usr_inpt = input()

            if usr_inpt == "exit":
                break;
            self.handle_command(usr_inpt)

    def handle_command(self, command):
        commands = Commands.format_input(command)

        if len(commands) == 0:
            return

        main_command = commands.pop(0)

        if main_command == "help":
            self.main_help()
            return

        status = self.commands.handle_command(main_command, commands)

        if status == False:
            self.set_style(self.RED_FG, content=f"Command '{main_command}' not found\n")
    def main_help(self):
        array_commands = self.commands.get_list()
        
        commands = "\n\t".join(array_commands)

        print(f"""
Commands:
    {commands}
""")

    
if __name__ == "__main__":
    console = Console()
    console.run()