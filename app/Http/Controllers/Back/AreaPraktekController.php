<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\Artikel;
use App\Models\Asset;
use App\Models\Content;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response as res;
use Intervention\Image\Facades\Image;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Protection;

class AreaPraktekController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $area_praktek = Content::where('section_id','=',7)->whereNull('parent_content_id')->whereNull('deleted_at')->first();
        if($area_praktek) {
            $data['model'] = Content::find($area_praktek->id);
            $data['content'] = json_decode($data['model']->content);
        } else {
            $data['model'] = new Content();
        }
        return view('back.area_praktek.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $area_praktek = Content::where('section_id','=',7)->whereNull('parent_content_id')->whereNull('deleted_at')->first();
        if($area_praktek) {
            $data['parent_content'] = Content::find($area_praktek->id);
            $data['model'] = new Content();
        }
        return view('back.area_praktek.form', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(isset($request->parent_content_id)) {
            $request->validate([
                'title' => 'required',
                'short_description' => 'max:140',
                'active' => 'required',
            ],
            [
                'title.required' => 'Judul Area Praktek Wajib Diisi',
                'short_description.max' => 'Deskripsi Singkat Maksimal 140 karakter',
                'active.required' => 'Keterangan Wajib Diisi',
                
            ]);
            $parent_content_id = base64_decode($request->parent_content_id);
            $active = $request->active;
        } else {
            $parent_content_id = null;
            $active = 1;
        }

        $content = $request->content;

        if(isset($content)) {
            $dom = new \DomDocument();
            @$dom->loadHtml($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            $imageFile = $dom->getElementsByTagName('imageFile');
        
            foreach($imageFile as $item => $image){
                $data = $content->getAttribute('src');
                list($type, $data) = explode(';', $data);
                list(, $data)      = explode(',', $data);
                $imgeData = base64_decode($data);
                $image_name= "/upload/" . time().$item.'.png';
                $path = public_path() . $image_name;
                file_put_contents($path, $imgeData);
                
                $image->removeAttribute('src');
                $image->setAttribute('src', $image_name);
            }
            $content = $dom->saveHTML();
        }

        $model = new Content();

        $model->title = $request->title;
        $model->subtitle = $request->subtitle;
        $model->slug = $this->textToSlug($request->title);
        $model->short_description = $request->short_description;
        $model->content = $content;
        $model->section_id = 7;
        $model->active = $active;
        $model->parent_content_id = $parent_content_id;
        $model->save();

        if($request->file('gambar')) {
            $fileName = time().'-'.Auth::user()->id.'-area-praktek-'.$request->file('gambar')->hashName();
            $request->file('gambar')->move(public_path('frontend/assets/img/resize'), $fileName);

            $image = $request->file('gambar');
            $img = Image::make(public_path('frontend/assets/img/resize/'.$fileName));
            $img->resize(650,430);
            $img->save(public_path('frontend/assets/img/'.$fileName));

            unlink(public_path('frontend/assets/img/resize/'.$fileName));

            $asset = new Asset();
            $asset->thumbnail = $fileName;
            $asset->content_id = $model->id;
            $asset->keterangan = "thumbnail";
            $asset->save();
        }

        if ($model->save()) {
            return redirect()->route('administrator.area-praktek.index')->with('alert.success', 'Area Praktek Berhasil Disimpan');
        } else {
            return redirect()->route('administrator.area-praktek.create')->with('alert.failed', 'Something Wrong');
        }
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $model = Content::find($id);
        return res::json($model);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $id = base64_decode($id);
        $area_praktek = Content::where('section_id','=',7)->whereNull('parent_content_id')->whereNull('deleted_at')->first();
        if($area_praktek) {
            $data['parent_content'] = Content::find($area_praktek->id);
            $data['model'] = Content::find($id);
        }

        return view('back.area_praktek.form', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if(isset($request->parent_content_id)) {
            $request->validate([
                'title' => 'required',
                'short_description' => 'max:140',
                'active' => 'required',
            ],
            [
                'title.required' => 'Judul Area Praktek Wajib Diisi',
                'short_description.max' => 'Deskripsi Singkat Maksimal 140 karakter',
                'active.required' => 'Keterangan Wajib Diisi',
            ]);
            $parent_content_id = base64_decode($request->parent_content_id);
            $active = $request->active;
        } else {
            $parent_content_id = null;
            $active = 1;
        }

        $content = $request->content;

        if(isset($content)) {
            $dom = new \DomDocument();
            @$dom->loadHtml($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            $imageFile = $dom->getElementsByTagName('imageFile');
        
            foreach($imageFile as $item => $image){
                $data = $content->getAttribute('src');
                list($type, $data) = explode(';', $data);
                list(, $data)      = explode(',', $data);
                $imgeData = base64_decode($data);
                $image_name= "/upload/" . time().$item.'.png';
                $path = public_path() . $image_name;
                file_put_contents($path, $imgeData);
                
                $image->removeAttribute('src');
                $image->setAttribute('src', $image_name);
            }
            $content = $dom->saveHTML();
        }

        $id = base64_decode($id);
        $model = Content::find($id);
        $asset = Asset::where('content_id',$model->id)->first();

        if($request->file('gambar')) {
            $fileName = time().'-'.Auth::user()->id.'-area-praktek-'.$request->file('gambar')->hashName();
            $request->file('gambar')->move(public_path('frontend/assets/img/resize'), $fileName);

            $image = $request->file('gambar');
            $img = Image::make(public_path('frontend/assets/img/resize/'.$fileName));
            $img->resize(650,430);
            $img->save(public_path('frontend/assets/img/'.$fileName));

            unlink(public_path('frontend/assets/img/resize/'.$fileName));

            if($asset) {
                if(file_exists(public_path('frontend/assets/img/'.$asset->thumbnail))) {
                    unlink(public_path('frontend/assets/img/'.$asset->thumbnail));
                }

                $asset_model = Asset::find($asset->id);
                $asset_model->thumbnail = $fileName;
                $asset_model->content_id = $model->id;
                $asset_model->save();
            } else {
                $asset_model = new Asset();
                $asset_model->thumbnail = $fileName;
                $asset_model->content_id = $model->id;
                $asset_model->keterangan = "thumbnail";
                $asset_model->save();
            }
        }

        $model->title = $request->title;
        $model->subtitle = $request->subtitle;
        $model->slug = $this->textToSlug($request->title);
        $model->short_description = $request->short_description;
        $model->content = $content;
        $model->section_id = 7;
        $model->active = $active;
        $model->parent_content_id = $parent_content_id;
        $model->save();

        if ($model->save()) {
            return redirect()->route('administrator.area-praktek.index')->with('alert.success', 'Area Praktek telah Diperbaharui');
        } else {
            return redirect()->route('administrator.area-praktek.create')->with('alert.failed', 'Something Wrong');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $id = base64_decode($id);
        $model = Content::find($id);
        $asset = Asset::where('content_id',$model->id)->first();
        if($asset) {
            if(file_exists(public_path('frontend/assets/img/'.$asset->thumbnail))) {
                unlink(public_path('frontend/assets/img/'.$asset->thumbnail));
            }

            $asset_model = Asset::find($asset->id);
            $asset_model->deleted_at = date('Y-m-d H:i:s');
            $asset_model->deleted_by = Auth::user()->id;
            $asset_model->save();
        }

        $model->deleted_at = date('Y-m-d H:i:s');
        $model->deleted_by = Auth::user()->id;
        $model->save();
    }

    public function datatable(Request $request)
    {
        $query = Content::where('section_id','=',7)->whereNotNull('parent_content_id')->orderBy('id','desc');
        return DataTables::of($query)
            ->addColumn('action', function ($model) {
                $string = '<div class="btn-group">';
                $string .= '<a href="' . route('administrator.area-praktek.edit', ['id' => base64_encode($model->id)]) . '" type="button"  class="btn btn-sm btn-info" title="Edit Area Praktek"><i class="fas fa-edit"></i></a>';
                $string .= '&nbsp;&nbsp;<a href="' . route('administrator.area-praktek.destroy', ['id' => base64_encode($model->id)]) . '" type="button" class="btn btn-sm btn-danger btn-delete" title="Remove"><i class="fa fa-trash"></i></a>';
                $string .= '</div>';
                return $string;
            })
            ->editColumn('thumbnail', function ($model) {
                $string = '';
                $asset = Asset::where('content_id',$model->id)->first();
                if($asset) {
                    $string = '<img src="'.asset('frontend/assets/img/'.$asset->thumbnail).'" width="200px" height="75px">';
                }
                return $string;
            })
            ->addIndexColumn()
            ->rawColumns(['action','thumbnail'])
            ->make(true);
    }

    private function textToSlug($text='') {
        $text = trim($text);
        if (empty($text)) return '';
          $text = preg_replace("/[^a-zA-Z0-9\-\s]+/", "", $text);
          $text = strtolower(trim($text));
          $text = str_replace(' ', '-', $text);
          $text = $text_ori = preg_replace('/\-{2,}/', '-', $text);
          return $text;
    }
}
