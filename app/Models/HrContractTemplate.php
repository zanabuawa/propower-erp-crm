<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class HrContractTemplate extends Model
{
    use BelongsToCompany, SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'code',
        'description',
        'contract_type',
        'duration_months',
        'work_shift',
        'work_hours_per_week',
        'work_days',
        'entry_time',
        'exit_time',
        'saturday_hours',
        'benefits',
        'print_custom_clauses',
        'print_pages',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'duration_months'     => 'integer',
        'work_hours_per_week' => 'integer',
        'work_days'           => 'array',
        'saturday_hours'      => 'decimal:2',
        'benefits'            => 'array',
        'print_pages'         => 'array',
        'is_active'           => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getDurationLabelAttribute(): string
    {
        return $this->duration_months
            ? $this->duration_months.' meses'
            : 'Sin duracion fija';
    }

    public function getPrintPagesForEditingAttribute(): array
    {
        return array_pad(array_slice($this->print_pages ?: self::defaultPrintPages(), 0, 5), 5, '');
    }

    public static function ensureDefaultsForCompany(int $companyId, ?int $userId = null): void
    {
        $base = [
            'company_id' => $companyId,
            'contract_type' => 'temporal',
            'duration_months' => 3,
            'work_shift' => 'campo',
            'work_hours_per_week' => 48,
            'work_days' => [1, 2, 3, 4, 5, 6],
            'entry_time' => '07:00',
            'exit_time' => '18:00',
            'saturday_hours' => 5,
            'benefits' => [
                'aguinaldo_days' => 15,
                'vacation_days' => 6,
                'vacation_premium_pct' => 25,
            ],
            'print_pages' => self::defaultPrintPages(),
            'is_active' => true,
            'created_by' => $userId,
        ];

        self::firstOrCreate([
            'company_id' => $companyId,
            'code' => 'INICIAL-3M',
        ], $base + [
            'name' => 'Contrato inicial 3 meses',
            'code' => 'INICIAL-3M',
            'description' => 'Plantilla base para el primer contrato temporal de tres meses.',
        ]);

        self::firstOrCreate([
            'company_id' => $companyId,
            'code' => 'RENOVACION-6M',
        ], array_merge($base, [
            'name' => 'Renovacion 6 meses',
            'code' => 'RENOVACION-6M',
            'duration_months' => 6,
            'description' => 'Plantilla base para el segundo contrato temporal de seis meses.',
        ]));

        self::firstOrCreate([
            'company_id' => $companyId,
            'code' => 'INDEFINIDO',
        ], array_merge($base, [
            'name' => 'Contrato indefinido',
            'code' => 'INDEFINIDO',
            'contract_type' => 'indefinido',
            'duration_months' => null,
            'description' => 'Plantilla base para contrato por tiempo indeterminado.',
        ]));

        self::where('company_id', $companyId)
            ->where('code', 'RENOVACION-3M')
            ->update(['is_active' => false]);

        self::where('company_id', $companyId)
            ->whereNull('print_pages')
            ->update(['print_pages' => json_encode(self::defaultPrintPages())]);
    }

    public static function defaultPrintPages(): array
    {
        return [
            "CONTRATO INDIVIDUAL DE TRABAJO\n\nEn la ciudad de {{city}}, estado de {{state}}, siendo el dia {{start_date_long}}, los que suscriben el presente, a saber, C. {{company_name}} con RFC: {{company_rfc}}, representada por C. {{employer_rep}} con RFC: {{employer_rep_rfc}} CURP: {{employer_rep_curp}}, nacionalidad: MEXICANA edad: {{employer_rep_age}} AÑOS; Sexo: {{employer_rep_gender}}, domicilio: {{employer_address}}, quien en el curso del presente contrato se denominara \"EL PATRON\", y, por la otra C. {{employee_name}}, con RFC: {{employee_rfc}}, CURP: {{employee_curp}}, nacionalidad: MEXICANA, edad: {{employee_age}} AÑOS; sexo: {{employee_gender}}, quien en el curso del presente contrato se denominara \"EL TRABAJADOR\", hacemos constar que hemos convenido celebrar un CONTRATO INDIVIDUAL DE TRABAJO, al tenor de las siguientes:\n\nCLAUSULAS.\n\nPRIMERA. Para los efectos del articulo 25 de la Ley Federal de Trabajo, manifiesta \"EL PATRON\" llamarse como ha quedado asentado en el proemio, y que esta se dedica al ramo de ELECTROCONSTRUCCIONES, entre otras del objeto social; Asi mismo \"EL TRABAJADOR\" declara llamarse como quedo escrito en el proemio, por su propio derecho. Reconociendo el trabajador el citado domicilio como unica fuente de trabajo para todos los efectos legales a que haya lugar y con el conocimiento de que es contratado para que preste sus servicios personales subordinados juridicamente a la patronal unica y exclusivamente este como unico patron, mediante el pago de un salario por virtud del presente contrato de trabajo.\n\nSEGUNDA. \"EL TRABAJADOR\" se obliga a prestar sus servicios personales para \"EL PATRON\" subordinado juridicamente a este, con el puesto de {{position}} consistiendo sus funciones en: las inherentes a dicho puesto, asi como los mandados fuera de la negociacion y todas las inherentes al puesto que desempenara actividades que se senalan unicamente en forma enunciativa y no limitativa. Este trabajo debera ejecutarlo con esmero y eficiencia. Queda expresamente convenido que acatara en el desempeno de su trabajo, todas las disposiciones que dicte el patron, asi como las contenidas en el reglamento interior del trabajo y todos los ordenamientos legales que le sean aplicables.\n\nTERCERA. \"EL TRABAJADOR\" debera ejecutar su trabajo en el local y/o oficinas del patron mismas que se encuentran ubicadas en {{workplace}}, o bien en cualquier lugar que el patron le ordene, en cualquier parte de la republica donde opere la sociedad patronal, otorgando desde este momento el trabajador su consentimiento expreso para ello, por requerir el personal en la consecucion del objeto social del patron y que es el unico patron del trabajador contratado por el presente instrumento.\n\nCUARTA. Este contrato se celebra por {{contract_type_text}}, {{contract_term_clause}}.\n\nQUINTA. \"EL TRABAJADOR\" percibira, por la prestacion de los servicios a que se refiere este contrato, un salario diario de \${{daily_salary}} pesos mexicanos.\n\n\"EL TRABAJADOR\" conviene, acepta y otorga su consentimiento expreso, para que \"EL PATRON\" le deposite el pago de su salario y demas prestaciones mediante deposito en cuenta de banco a nombre de \"EL TRABAJADOR\", pudiendo este retirar las cantidades depositadas directamente de ventanilla en el banco, o de cajero automatico mediante tarjeta de debito, o bien, haciendose el retiro por cualquier otro procedimiento que permita la disponibilidad del dinero en efectivo. El deposito en la cuenta bancaria se podra hacer por la totalidad del salario y demas prestaciones que le correspondan o bien, se podra hacer el deposito en la cuenta bancaria de solo una parte del salario o las prestaciones correspondientes y el resto pagarsele en dinero en efectivo.\n\nSEXTA. Queda expresamente convenido que el trabajador no podra dedicarse (ni aun a titulo gratuito) a la venta de ningun otro producto o bien ya sea mueble o inmueble de ninguna otra empresa dentro de su jornada de trabajo, y que, fuera de su jornada de trabajo, le estara estrictamente prohibido dedicarse a cualquier labor que signifique en cualquier forma una competencia para las actividades del patron, en caso de inobservancia de la presente clausula se considerara causal de rescision de la relacion laboral sin responsabilidad para el patron por causas imputables al trabajador.\n\nSEPTIMA. La duracion de la jornada de trabajo sera de {{work_hours_per_week}} horas a la semana, conforme al horario de {{entry_time}} a {{exit_time}} horas, en los dias {{work_days_label}}, para lo cual el trabajador concede al",
            "patron la facultad de repartir o combinar los horarios de trabajo de acuerdo a las necesidades de la empresa sin que por ello se entienda como una modificacion unilateral de las condiciones de trabajo, toda vez que el trabajador desde este momento otorga consentimiento para ello, de conformidad con el articulo 59 de la Ley Federal del Trabajo.\n\nOCTAVA. Cuando por circunstancias extraordinarias se aumente la jornada de trabajo, los servicios prestados durante el tiempo excedente se consideraran como extraordinarios.\n\nNOVENA. \"EL TRABAJADOR\" esta obligado a checar su tarjeta o a firmar las listas de asistencia, a la hora de entrada y a la hora de salida de sus labores, su periodo para descansar y tomar sus alimentos sera designado conforme al horario que determine el patron.\n\nDECIMA. Por cada seis dias de trabajo, tendra el trabajador un descanso semanal de un dia, con pago de salario integro, conviniendose en que dicho descanso lo disfrutara el dia que la empresa considere prudente debido al giro de la misma. Tambien disfrutara del pago senalado segun la ley, los dias senalados en el articulo 74 de la Ley Federal del Trabajo, a saber: el 1 de enero, el primer lunes de febrero, el tercer lunes de marzo, el 1 de mayo, el 16 de septiembre, el tercer lunes de noviembre, el 25 de diciembre y el 1 de diciembre de cada seis anos cuando corresponda a la transmision del Poder Ejecutivo Federal.\n\nDECIMA PRIMERA. \"EL TRABAJADOR\", despues de un ano de servicios continuos disfrutara de un periodo anual de vacaciones pagadas, de {{vacation_days}} dias laborales, que aumentara en dos dias laborales, hasta llegar a veinte, por cada ano subsecuente de servicios. Despues del sexto ano, el periodo de vacaciones aumentara en dos dias por cada cinco anos de servicios. Los salarios correspondientes a las vacaciones se cubriran con una prima del {{vacation_premium}} por ciento sobre los mismos.\n\nEn caso de faltas injustificadas de asistencia al trabajo, se podran deducir dichas faltas del periodo de prestacion de servicios computable para fijar las vacaciones, reduciendose estas proporcionalmente.\n\nLas vacaciones no podran compensarse con una remuneracion economica. Si la relacion de trabajo termina antes de que se cumpla el ano de servicios, el trabajador tendra derecho a una remuneracion proporcionada al tiempo de servicios prestados.\n\nDECIMA SEGUNDA. \"EL TRABAJADOR\" percibira un aguinaldo anual, que debera pagarsele antes del dia veinte de diciembre, equivalente a {{aguinaldo_days}} dias de salario.\n\nCuando no haya cumplido el ano de servicios, tendra derecho a que se le pague en proporcion al tiempo trabajado.\n\nDECIMA TERCERA. \"EL TRABAJADOR\" conviene en someterse a los reconocimientos medicos que periodicamente ordene el patron en los terminos de la fraccion X del articulo 134 de la Ley Federal del Trabajo; en la inteligencia de que el medico que los practique sera designado y retribuido por el patron.\n\nDECIMA CUARTA. \"EL TRABAJADOR\" sera capacitado o adiestrado en los terminos de los planes y programas establecidos, o que se establezcan, por el patron, conforme a lo dispuesto en el Capitulo II Bis, Titulo Cuarto de la Ley Federal del Trabajo.\n\nDECIMA QUINTA. Para los efectos de su antiguedad, queda establecido que el trabajador ingreso a prestar sus servicios personales subordinados mediante pago de un salario para el patron en el dia {{start_date_long}}.\n\nDECIMA SEXTA. Las partes convienen que todo lo no previsto en el presente contrato se regira por lo dispuesto en el Reglamento Interior del Trabajo, la Ley Federal del Trabajo, y en que, para todo lo que se refiera a interpretacion, ejecucion y cumplimiento del mismo, se someten expresamente a la jurisdiccion y competencia de la autoridad laboral correspondiente.\n\nDECIMA SEPTIMA. Queda expresamente convenido, que debido a la naturaleza de las labores que debe prestar \"EL TRABAJADOR\", este podra ser cambiado indistintamente del lugar que preste sus servicios, a cualquier lugar que el patron lo llegara a necesitar por motivo de su empleo y/o capacitacion, y tambien se le podra modificar el horario de labores, siempre dentro de la jornada legal, lo anterior de acuerdo a las necesidades del servicio.",
            "DECIMA OCTAVA. Se establece con fundamento en el Articulo 349 de la Ley Federal del Trabajo, como causal especial de la rescision de la relacion de trabajo por causas imputables al trabajador, el hecho que este no atienda con cortesia y esmero a la clientela del establecimiento, sin que esta clausula sea contraria a la Ley Laboral.\n\nVIGESIMA. \"EL TRABAJADOR\" se obliga a no revelar a terceras personas los nombres de los proveedores, informacion confidencial de las ventas que se efectuan en la fuente de trabajo, estudios, tecnicas que se utilicen en la fuente de trabajo, cartera de clientes, etc. o cualquier otro asunto relacionado con la empresa y/o de cualquier otra actividad de los cuales tenga conocimiento por razon del trabajo que va a realizar, asi como los asuntos administrativos cuya divulgacion pueda perjudicar a su patron mientras este vigente el presente contrato, siendo causal de rescision de la relacion de trabajo sin responsabilidad para su patron lo anterior.\n\nVIGESIMA PRIMERA. \"EL TRABAJADOR\" se obliga a presentarse a laborar con el uniforme correspondiente, el cual debera de portarlo con limpieza y pulcritud.\n\nAsi mismo \"EL TRABAJADOR\" faculta desde este momento a \"EL PATRON\" para que, en caso de no acatar las disposiciones anteriormente senaladas, este podra imponerle a \"EL TRABAJADOR\" las medidas disciplinarias correspondientes que podran ser:\n\na). - Una amonestacion verbal.\n\nb). - Una amonestacion por escrito.\n\nc). - Una suspension de uno a tres dias sin goce de sueldo.\n\nd). - La rescision del contrato y la relacion de trabajo por causas imputables a \"EL TRABAJADOR\".\n\nVIGESIMA SEGUNDA. \"EL TRABAJADOR\" faculta y autoriza expresamente a \"EL PATRON\" a efectuar descuentos en su salario por concepto de pago de uniforme que utiliza para el desempeno de sus labores el cual es de su exclusiva propiedad, descuentos que deberan apegarse a lo establecido por el articulo 110 y demas relativos de la Ley Federal del Trabajo.\n\nVIGESIMA TERCERA. \"EL TRABAJADOR\" reconoce que hasta la fecha se ha desempenado en una jornada legal de labores, asi como que se le ha cubierto hasta el dia de hoy de manera integra todas y cada una de las prestaciones de ley entre las que se encuentra el tiempo extraordinario cuando lo llego a laborar entre otras, con excepcion del aguinaldo proporcional a este ultimo ano.\n\nVIGESIMA CUARTA. \"EL TRABAJADOR\" reconoce que dada la naturaleza del empleo realiza, y con anterioridad ha realizado, trabajos de investigacion y perfeccionamiento de los procedimientos utilizados por el patron y los clientes de este, y que los realiza por instrucciones, bajo la supervision y con la asesoria del patron, asi como por cuenta de este, por lo que reconoce que la propiedad de cualesquier invencion y el derecho a la explotacion de la patente respectiva corresponderan al patron.\n\nVIGESIMA QUINTA. \"EL TRABAJADOR\" se obliga a cumplir con el articulo 21 de la Ley Federal De Proteccion De Datos Personales En Posesion De Los Particulares. El responsable o terceros que intervengan en cualquier fase del tratamiento de datos personales deberan guardar confidencialidad respecto a estos, obligacion que subsistira aun despues de finalizar sus relaciones con el titular o, en su caso, con el responsable.\n\nVIGESIMA SEXTA. \"EL PATRON\", se encuentra obligado a solicitar al beneficiario unico en caso de muerte del trabajador, se apersone ante la autoridad laboral o junta de conciliacion y arbitraje competente, a fin de garantizar que cuente con el caracter de beneficiario unico vigente, antes de realizar el pago correspondiente.\n\nVIGESIMA SEPTIMA. \"EL PATRON\" podra permitir el uso a \"EL TRABAJADOR\", de un automovil propiedad de \"EL PATRON\", unica y exclusivamente por motivo de las actividades propias de su trabajo, por lo que queda prohibido a \"EL TRABAJADOR\" hacer uso de dicho automovil para uso personal.",
            "Asi mismo, \"EL TRABAJADOR\", tiene la obligacion de devolver y guardar el vehiculo diariamente, despues de la jornada de trabajo, al lugar en donde le indique \"EL PATRON\".\n\nVIGECIMA OCTAVA. Con fundamento en el articulo 47 de la Ley Federal del Trabajo, son causas de rescision de la relacion de trabajo, sin responsabilidad para el patron:\n\nI. Enganarlo el trabajador o en su caso, el sindicato que lo hubiese propuesto o recomendado con certificados falsos o referencias.\n\nII. Incurrir el trabajador, durante sus labores, en faltas de probidad u honradez, actos de violencia, amagos, injurias o malos tratamientos.\n\nIII. Cometer el trabajador contra alguno de sus companeros cualquiera de los actos enumerados en la fraccion anterior.\n\nIV. Cometer el trabajador, fuera del servicio, contra el patron, sus familiares o personal directivo administrativo, actos graves.\n\nV. Ocasionar el trabajador, intencionalmente, perjuicios materiales durante el desempeno de las labores.\n\nVI. Ocasionar el trabajador perjuicios graves, sin dolo, pero con negligencia tal que sea la causa unica del perjuicio.\n\nVII. Comprometer el trabajador, por su imprudencia o descuido inexcusable, la seguridad del establecimiento o de las personas.\n\nVIII. Cometer el trabajador actos inmorales en el establecimiento o lugar de trabajo.\n\nIX. Revelar el trabajador los secretos de fabricacion o dar a conocer asuntos de caracter reservado, con perjuicio de la empresa.\n\nX. Tener el trabajador mas de tres faltas de asistencia en un periodo de treinta dias, sin permiso del patron o sin causa justificada.\n\nXI. Desobedecer el trabajador al patron o a sus representantes, sin causa justificada, siempre que se trate del trabajo contratado.\n\nXII. Negarse el trabajador a adoptar las medidas preventivas o a seguir los procedimientos indicados para evitar accidentes o enfermedades.\n\nXIII. Concurrir el trabajador a sus labores en estado de embriaguez o bajo la influencia de algun narcotico o droga enervante.\n\nXIV. La sentencia ejecutoriada que imponga al trabajador una pena de prision, que le impida el cumplimiento de la relacion de trabajo.\n\nXV. Las analogas a las establecidas en las fracciones anteriores, de igual manera graves y de consecuencias semejantes.",
            "Leido que fue el presente contrato por las partes, e impuestas de su contenido y fuerza legal, lo firmaron, quedando un tanto en poder de cada una de las mismas.\n\n\n\n________________________________\nC. {{employee_name}}\n\"EL TRABAJADOR\".\n\n\n________________________________\nING. {{employer_rep}}\n\"EL EMPLEADOR\".",
        ];
    }
}
