<!DOCTYPE html>
<html lang="en">

@include('Layout.head')




<body class="bg-white" onload='print()'>

    @yield('isi')



    @include('Layout.js')
    @yield('js')


</body>



</html>
