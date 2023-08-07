<?php

namespace App\Http\Controllers;

use App\Models\Detail;
use App\Models\Header;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffOrderController extends Controller
{
    public function showQueue(Request $request)
    {
        $key = $request->keyword;

        if($key == null)
        {
            $data = Header::paginate(6);
            $key = '';
        }
        else
        {
            $data = Header::where('id', 'LIKE', '%'.$key.'%')->
            orWhere('customer_id', 'LIKE', '%'.$key.'%')->
            orWhere('staff_id', 'LIKE', '%'.$key.'%')->paginate(6);
        }

        return view('staff.queue.index', ["queue"=>$data, "keyword"=>$key]);
    }

    public function showDetail(Request $request, $id)
    {
        $data = Detail::where('header_id', $id)->paginate(6);

        return view('staff.queue.details', ["details"=>$data]);
    }

    public function handleOrder($id)
    {
        $staffId = Auth::id();
        $data = Header::findOrFail($id);

        if($data == null)
        {
            return redirect()->route('showQueue.admin')->with('error_message', 'Error handling order.');
        }
        
        Header::where('id', $id)->update([
            'staff_id'=>$staffId,
            'updated_at'=>Carbon::now(),
        ]);

        return redirect()->route('showQueue.staff')->with('success_message', 'Success handling order!');
    }

    public function removeOrder($id)
    {
        $header = Header::findOrFail($id);

        if($header == null)
        {
            return redirect()->route('showQueue.staff')->with('error_message', 'Data does not exists, please refresh the page.');
        }

        $details = Detail::where('header_id', $header['id'])->get();

        foreach($details as $item)
        {
            $item->destroy($item['id']);
        }

        $header->destroy($header['id']);

        return redirect()->route('showQueue.staff')->with('success_message', 'Success removing order!');
    }

}