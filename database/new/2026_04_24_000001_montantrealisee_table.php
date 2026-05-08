<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIdTransfertCaisseToDecaissementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('decaissements', function (Blueprint $table) {
            $table->unsignedBigInteger('id_transfert_caisse')->after('statut_financement'); // change 'some_column_name' to actual column after which this should be added
            $table->foreign('id_transfert_caisse')->references('id')->on('transfert_caisse')->onDelete('cascade');
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('decaissements', function (Blueprint $table) {
            $table->dropForeign(['id_transfert_caisse']);
            $table->dropColumn('id_transfert_caisse');
        });
    }
}