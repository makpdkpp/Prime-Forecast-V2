@extends('adminlte::page')

@section('title', 'Reports | PrimeForecast')

@section('content_header')
    <h1>Reports</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-gavel"></i> รายงานวันยื่น Bidding</h3>
            </div>
            <div class="card-body">
                <p class="card-text">
                    สร้างรายงานข้อมูลการยื่น Bidding พร้อมฟิลเตอร์ผู้ใช้และช่วงวันที่
                </p>
                <ul>
                    <li>เลือกผู้ใช้ (ทั้งหมด หรือรายบุคคล)</li>
                    <li>เลือกช่วงวันที่ (วันเริ่มต้น - วันสิ้นสุด)</li>
                    <li>แสดงข้อมูล: ชื่อโครงการ | หน่วยงาน/บริษัท | มูลค่า | วันยื่น Bidding</li>
                    <li>ส่งออกเป็น Excel และ PDF</li>
                </ul>
                <a href="{{ route('admin.reports.bidding') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-right"></i> ไปยังรายงาน Bidding
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-file-signature"></i> รายงานวันเซ็นสัญญา</h3>
            </div>
            <div class="card-body">
                <p class="card-text">
                    สร้างรายงานข้อมูลการเซ็นสัญญา พร้อมฟิลเตอร์ผู้ใช้และช่วงวันที่
                </p>
                <ul>
                    <li>เลือกผู้ใช้ (ทั้งหมด หรือรายบุคคล)</li>
                    <li>เลือกช่วงวันที่ (วันเริ่มต้น - วันสิ้นสุด)</li>
                    <li>แสดงข้อมูล: ชื่อโครงการ | หน่วยงาน/บริษัท | มูลค่า | วันเซ็นสัญญา</li>
                    <li>ส่งออกเป็น Excel และ PDF</li>
                </ul>
                <a href="{{ route('admin.reports.contract') }}" class="btn btn-success">
                    <i class="fas fa-arrow-right"></i> ไปยังรายงานสัญญา
                </a>
            </div>
        </div>
    </div>
</div>
@stop
