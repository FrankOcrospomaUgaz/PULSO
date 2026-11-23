    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        /**
         * Run the migrations.
         */
        public function up() {
            Schema::create('tipopregunta', function (Blueprint $table) {
                $table->id();
                $table->string('nombre', 255);
                $table->string('descripcion', 255);
                $table->char('situacion', 1)->default('E');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        public function down() {
            Schema::dropIfExists('tipopregunta');
        }
    };
