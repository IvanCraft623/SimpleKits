[![](https://poggit.pmmp.io/shield.state/SimpleKits)](https://poggit.pmmp.io/p/SimpleKits)
[![](https://poggit.pmmp.io/shield.api/SimpleKits)](https://poggit.pmmp.io/p/SimpleKits)
[![](https://poggit.pmmp.io/shield.dl.total/SimpleKits)](https://poggit.pmmp.io/p/SimpleKits)

<div align="center">
  <h1>🔮 SimpleKits ⚔️</h1>
  <p>The best kits manager for pocketmine</p>
</div>

## Description:
Un administrador de kits fácil de usar para PocketMine.MP 4.0
## For Developers
Examples and tutorials can be found on the [SimpleKits Wiki](https://github.com/IvanCraft623/SimpleKits/wiki).

# Commands
Command | Description | Permission
--- | --- | ---
`/kit` | Claim selected kit. | none
`/kits` | See avaible kits. | simplekits.kits.command
`/simplekits` | Manage kits and players. | simplekits.simplekits.command
`/simplekits create <name>` | Create a kit. | simplekits.simplekits.command
`/simplekits kits` | Manage kits. | simplekits.simplekits.command
`/simplekits players [page]` | Players list to manage. | simplekits.simplekits.command
`/simplekits playerdata <name>` | Manage a player. | simplekits.simplekits.command

# Features

- Multi-languages support
- Forms for an easy interaction
- Asynchronous Database I/O
- MySQL DB Support
- SQLite3 DB Support
- Manage Players in-game
- Manage Kits in-game
- Easy Kits Creation
- Support Items with custon NamedTag
- An easy API for developers
- And more...!

# Tutorials

## Create a kit
To create a kit you can run the command `/simplekits create <name>` which will open a form, [view video](https://www.youtube.com/watch?v=5k2tT3mUNec).

## Enable/disable command
The `/kit` & `/kits` commands can be easily enabled or disabled using the config.yml file.
```yaml
# Enable or disable "/kit" command (Command to claim selected kit)
kit-command: true
# Enable or disable "/kits" command (Command to select a kit)
kits-command: true
```

# Project information
Version | Pocketmine API | PHP | Status
--- | --- | --- | ---
0.0.1 | [PM4](https://github.com/pmmp/PocketMine-MP/tree/stable) | 8 | Functional
