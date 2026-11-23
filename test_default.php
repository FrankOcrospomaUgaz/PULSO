<?php
echo json_encode(\DB::select("SELECT column_default FROM information_schema.columns WHERE table_name = 'movimiento' AND column_name = 'situacion'"));
