class Commands:

    def __init__(self):
        self.commands = {}

    def register_command(self, name, callback):
        self.commands[name] = callback
    
    def handle_command(self, name, args = []):
        if name in self.commands:
            self.commands[name](args)
            return True
        else:
            return False

    @staticmethod
    def format_input(rawInput):
        return rawInput.strip().split()

    def get_list(self):
        return list(self.commands.keys())