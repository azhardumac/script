<?php

namespace App\Observers;

use App\Events\NewChatEvent;
use App\Events\NewMessage;
use App\Models\Notification;
use App\Models\UserChat;
use Illuminate\Support\Facades\Config;

class NewChatObserver
{

    public function created(UserChat $userChat)
    {
        if (!isRunningInConsoleOrSeeding()) {
            event(new NewChatEvent($userChat));
            
            if (pusher_settings()->status == 1 && pusher_settings()->messages == 1) {
                Config::set('queue.default', 'sync'); // Set intentionally for instant delivery of messages
                broadcast(new NewMessage($userChat))->toOthers()->via('pusher');
            }
        }
    }

    public function creating(UserChat $userChat)
    {
        if (company()) {
            $userChat->company_id = company()->id;
        }
    }

    public function deleting(UserChat $userChat)
    {
        $notifyData = ['App\Notifications\NewChat'];

        \App\Models\Notification::deleteNotification($notifyData, $userChat->id);

    }

}
