# BetterSystems
An plugin for a better systems
# API

**Events**
```php
use Toxic\events\PlayerBanEvent; /** There are also PlayerMuteEvent, PlayerUnBanEvent & PlayerUnMuteEvent */

public function onBan(PlayerBanEvent $event){
    $player = $event->getPlayer();
    $whoBannedPlayer = $event->getBanner();
    $whoGotBan = $event->getWho();
    $wgb = Server::getInstance()->getPlayerExact($whoGotBan);
    $wbp = Server::getInstance()->getPlayerExact($whoBannedPlayer);

    /** Something random i guess */
    $wgb->sendMessage("You'd probably not beable to see this message but we ignore that, the person who banned u is $whoBannedPlayer, yeah");
}
```

**Provider API**
*Provider api is a bit difficult to explain however most functions u must get by uuid not player name, why? to make sure if the player changes there name they wont *
*Make new data and bypass the ban, so we use uuid to prevent these type of things to happen*
*However i did make some other functions one for getting by uuid and other by getting by playername/username*
*heres the most useful*
```php
use Toxic\BetterSystems;

public function example(Player $player){
   $uuid = $player->getUniqueId()->toString();
   $username = $player->getName();
   $provider = BetterSystems::getInstance()->getProvider();
   $isbannedByUUID = $provider->isBanned($uuid);
   $isbannedByUsername = $provider->isBannedByUsername($username);
   $ismutedbyUUID = $provider->isMuted($uuid);
   $ismutedbyUsername = $provider->isMutedByUsername($username);
   $banPlayer = $provider->banPlayer($uuid, 60, "No reason at all"); /** Bans player for 60 seconds, reason is No reason at all */
   $mutePlayer = $provider->mutePlayer($uuid, 60, "No reason at all"); /** Mutes player for 60 seconds, reason is No reason at all */
   $unmutePlayer = $provider->unmutePlayer($uuid);
   $unmutePlayerByUsername = $provider->unmutePlayerByUsername($username);
   $unbanPlayer = $provider->unbanPlayer($uuid);
   $unbanPlayerByUsername = $provider->unbanPlayerByUsername($username);
   /** Perm related */
   $permBanPlayer = $provider->permbanPlayer($uuid, "No reason at all");
   $permMutePlayer = $provider->permmutePlayer($uuid, "No reason at all");
   $permUnMutePlayer = $provider->unpermmutePlayer($uuid);
   $permUnBanPlayer = $provider->unpermbanPlayer($uuid);
   $ispermban = $provider->isPermBan($uuid);
   $ispermmuted = $provider->isPermMuted($uuid)
   $ispermbanByUsername = $provider->isPermBanByUsername($username);
   $ispermmutedByUsername = $provider->isPermMuteByUsername($username);
}
```


# Todo-List
- [X] Basics
- [X] Plugin Base, etc
- [X] Commands
- [X] Ban System
- [X] Kick System
- [X] Mute System   
- [X] Form Support
- [X] MultiProviders [MySQL & Sqlite3]
- [X] Custom Events
- [X] API [Kinda?]
