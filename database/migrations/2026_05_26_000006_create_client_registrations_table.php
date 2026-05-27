<?php

use App\Models\Client;
use App\Models\ClientRegistration;
use App\Models\Contract;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('number');
            $table->timestamps();

            $table->unique(['client_id', 'number']);
        });

        Schema::table('contracts', function (Blueprint $table) {
            $table->foreignId('client_registration_id')
                ->nullable()
                ->after('client_id')
                ->constrained('client_registrations')
                ->nullOnDelete();
        });

        Client::query()->get()->each(function (Client $client): void {
            $registration = ClientRegistration::create([
                'client_id' => $client->id,
                'number' => sprintf('MAT-%05d', $client->id),
            ]);

            Contract::query()
                ->where('client_id', $client->id)
                ->whereNull('client_registration_id')
                ->update(['client_registration_id' => $registration->id]);
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('client_registration_id');
        });

        Schema::dropIfExists('client_registrations');
    }
};
