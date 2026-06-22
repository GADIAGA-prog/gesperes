<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    public function viewAny(User $user): bool { return $user->can('documents.view'); }
    public function view(User $user, Document $d): bool { return $user->can('documents.view'); }
    public function create(User $user): bool { return $user->can('documents.upload'); }
    public function download(User $user, Document $d): bool { return $user->can('documents.download'); }
    public function delete(User $user, Document $d): bool { return $user->can('documents.delete'); }
}
