<?php
declare(strict_types=1);

/** Compatibilidad de contenido publicado; conserva los datos operativos y las fotos personalizadas. */
final class PortalContent
{
    /** Rutas PNG de las instalaciones anteriores y sus retratos JPG publicados. */
    private const LEGACY_PORTRAITS = [
        '/assets/marcona/docente-donayre-1a37db58a8.png' => '/assets/marcona/docente-donayre-a5a3b4966b.jpg',
        '/assets/marcona/docente-canales-7dd06e5dfd.png' => '/assets/marcona/docente-canales-b4ba41f4f7.jpg',
        '/assets/marcona/docente-turcke-e0fc0ee213.png' => '/assets/marcona/docente-turcke-e914f9b4a3.jpg',
        '/assets/marcona/docente-chumpe-c04263d1a1.png' => '/assets/marcona/docente-chumpe-e5d2acf40a.jpg',
        '/assets/marcona/docente-cota-cb0d14be4e.png' => '/assets/marcona/docente-cota-ac8f6cc058.jpg',
        '/assets/marcona/docente-chura-06af957359.png' => '/assets/marcona/docente-chura-a2536eeb40.jpg',
        '/assets/marcona/docente-jimenez-e7d6d50da9.png' => '/assets/marcona/docente-jimenez-66beedba44.jpg',
        '/assets/marcona/docente-huaman-8c17bf58ac.png' => '/assets/marcona/docente-huaman-c3d6ff3ee9.jpg',
        '/assets/marcona/docente-romero-ee349b01bf.png' => '/assets/marcona/docente-romero-50de3ad581.jpg',
        '/assets/marcona/docente-loayza-a800583ac2.png' => '/assets/marcona/docente-loayza-2213a4a35d.jpg',
        '/assets/marcona/docente-llerena-9808c5acc5.png' => '/assets/marcona/docente-llerena-c53a320172.jpg',
        '/assets/marcona/docente-quijaite-cdf29c122c.png' => '/assets/marcona/docente-quijaite-2a120a7177.jpg',
        '/assets/marcona/docente-delcarpio-a0d89095df.png' => '/assets/marcona/docente-delcarpio-963d977bec.jpg',
        '/assets/marcona/docente-romani-07c2a61b3f.png' => '/assets/marcona/docente-romani-c0d78e4e3c.jpg',
        '/assets/marcona/docente-llamccaya-3ed95f91ac.png' => '/assets/marcona/docente-llamccaya-605a437b34.jpg',
        '/assets/marcona/docente-guerrero-5d0895cd9f.png' => '/assets/marcona/docente-guerrero-bee4b28bc3.jpg',
        '/assets/marcona/docente-cuevas-c0a69d4d41.png' => '/assets/marcona/docente-cuevas-0ac88f8539.jpg',
        '/assets/marcona/docente-lopez-285e63336f.png' => '/assets/marcona/docente-lopez-965cbe02e5.jpg',
        '/assets/marcona/docente-rojasd-3e92c6c9b2.png' => '/assets/marcona/docente-rojasd-716b67f57d.jpg',
        '/assets/marcona/docente-ticona-0fbb271636.png' => '/assets/marcona/docente-ticona-6c0d050c4c.jpg',
        '/assets/marcona/docente-garibay-2cf8b96c35.png' => '/assets/marcona/docente-garibay-39df06fb4f.jpg',
        '/assets/marcona/docente-alvarez-fcb7b627fe.png' => '/assets/marcona/docente-alvarez-013baf9278.jpg',
        '/assets/marcona/docente-urbina-6ab6b56eb6.png' => '/assets/marcona/docente-urbina-681152850c.jpg',
        '/assets/marcona/docente-rojasr-83e071470d.png' => '/assets/marcona/docente-rojasr-228a0220b6.jpg',
        '/assets/marcona/docente-pareja-b16085b007.png' => '/assets/marcona/docente-pareja-ac75d07e0a.jpg',
    ];

    public static function forJson(array $payload): array
    {
        if (isset($payload['data']['academico'])) {
            $payload['data']['academico']['publicados'] = (object)($payload['data']['academico']['publicados'] ?? []);
        }
        return $payload;
    }

    public static function normalize(array $data): array
    {
        // Valores que el navegador espera en la configuración: se completan en el servidor para que ningún perfil
        // envíe cambios de configuración por el solo hecho de abrir el panel.
        if(isset($data['config']['mpv'])&&is_array($data['config']['mpv'])&&!isset($data['config']['mpv']['digitos']))$data['config']['mpv']['digitos']=6;
        // Distrito y región de la institución (instalaciones anteriores): se toman de las dos últimas partes de la dirección.
        if(isset($data['config']['inst'])&&is_array($data['config']['inst'])&&!isset($data['config']['inst']['region'])){
            $parts=array_values(array_filter(array_map('trim',explode(',',(string)($data['config']['inst']['direccion']??''))),'strlen'));
            $data['config']['inst']['region']=count($parts)>=2?$parts[count($parts)-1]:'';
            $data['config']['inst']['distrito']=count($parts)>=3?$parts[count($parts)-2]:'';
        }
        // «Impacto institucional» era Impacto con los colores del portal; desde que todas las estructuras usan esos colores es la misma.
        if(($data['config']['diseno']??null)==='impacto-flv')$data['config']['diseno']='impacto';
        if(isset($data['config']['disenoColores']['impacto-flv'])&&is_array($data['config']['disenoColores'])){
            if(empty($data['config']['disenoColores']['impacto']))$data['config']['disenoColores']['impacto']=$data['config']['disenoColores']['impacto-flv'];
            unset($data['config']['disenoColores']['impacto-flv']);
        }
        if(($data['__v']??0)<14){
            $data['academico']['perms']['acad']['config']=2;
            $data['academico']['perms']['dir']['config']=1;
            $data['__v']=14;
        }
        // Corrige únicamente las referencias antiguas conocidas, también al leer una BD existente.
        foreach ($data['personas'] ?? [] as $i => $person) {
            $photo = $person['foto'] ?? null;
            $replacement = is_string($photo) ? (self::LEGACY_PORTRAITS[$photo] ?? null) : null;
            if ($replacement !== null && is_file(dirname(__DIR__).'/public_html'.$replacement)) {
                $data['personas'][$i]['foto'] = $replacement;
            }
        }
        foreach ($data['tupa'] ?? [] as $i => $row) {
            $categories = ['6.- CONSTANCIA Y CERTIFICADOS' => '6.- CONSTANCIAS Y CERTIFICADOS',
                '12.-ALQUILERES' => '12.- ALQUILERES'];
            if (isset($categories[$row['categoria'] ?? ''])) {
                $data['tupa'][$i]['categoria'] = $categories[$row['categoria']];
            }
            $known = ['4.1' => ['4.1 ATRÍCULA REGULAR', '4.1 MATRÍCULA REGULAR'],
                '10.6' => ['10.6 CURSO DE OFIRMÁTICA Y/O EXCEL (PRESENCIAL) - MES 3',
                    '10.6 CURSO DE OFIMÁTICA Y/O EXCEL (PRESENCIAL) - MES 3']];
            $fix = $known[(string)($row['codigo'] ?? '')] ?? null;
            if ($fix && ($row['denominacion'] ?? '') === $fix[0]) {
                $data['tupa'][$i]['denominacion'] = $fix[1];
            }
        }
        return self::fitLimits($data);
    }

    /** Recorta los textos cargados que superan el límite del formulario; si no, el registro no se puede volver a guardar. */
    private static function fitLimits(array $data): array
    {
        foreach (PortalPolicy::defaults()['specs'] as $spec) {
            $key = $spec['collection'] ?? '';
            if (!isset($data[$key]) || !is_array($data[$key]) || !array_is_list($data[$key])) continue;
            foreach ($spec['fields'] as $field) {
                $max = $field['max'] ?? null;
                if (!is_int($max) || !in_array($field['type'] ?? 'text', ['text', 'textarea'], true)) continue;
                foreach ($data[$key] as $i => $row) {
                    $value = is_array($row) ? ($row[$field['name']] ?? null) : null;
                    if (!is_string($value) || str_contains($value, '<') || preg_match_all('/./us', $value) <= $max) continue;
                    if ($key === 'personas' && $field['name'] === 'titulo') $value = preg_replace('/\bMag\./u', 'Mg.', $value);
                    $value = self::cut($value, $max);
                    if (!empty($field['unique']) && in_array(strtolower(trim($value)), array_map(fn($other) => strtolower(trim((string)($other[$field['name']] ?? ''))), $data[$key]), true)) continue;
                    $data[$key][$i][$field['name']] = $value;
                }
            }
        }
        if(isset($data['academico'], $data['certificados'])&&class_exists(AcademicDocuments::class)){
            AcademicDocuments::sync($data);
            foreach(['certs'=>'cert','titulos'=>'tit'] as $key=>$prefix)foreach($data['academico'][$key] as &$row){
                $doc=PortalPolicy::find($data['certificados'],'ref','ix:'.$prefix.':'.$row['id']);if(!$doc)continue;
                $row+=['institucion'=>$doc['institucion']??'','estadoDoc'=>$doc['estado']??'Válido'];
                if($key==='certs')$row+=['horasDoc'=>$doc['horas']??'','tipoHoras'=>$doc['tipoHoras']??'Académicas'];
                else $row+=['denominacion'=>$doc['tipo'],'nivel'=>$doc['nivel']??'Superior tecnológico','pais'=>$doc['pais']??'Perú','region'=>$doc['region']??($data['config']['inst']['region']??'')];
            }unset($row);
        }
        return $data;
    }

    private static function cut(string $text, int $max): string
    {
        $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);
        if (count($chars) <= $max) return $text;
        $short = rtrim(implode('', array_slice($chars, 0, $max - 1)));
        $word = preg_replace('/\s+\S*$/u', '', $short);
        if ($word !== '' && preg_match_all('/./us', $word) >= $max * 0.6) $short = $word;
        return rtrim($short, " ,;:.") . '…';
    }
}
