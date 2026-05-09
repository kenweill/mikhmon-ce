#NoTrayIcon
#include <GUIConstantsEx.au3>
#include <WindowsConstants.au3>
#include <StaticConstants.au3>
#include <EditConstants.au3>
#include <ButtonConstants.au3>
#include <File.au3>
#include <MsgBoxConstants.au3>
#include <TrayConstants.au3>

; ============================================================
; MikhMon CE Server Launcher
; Community Edition - Open Source (GPL v2)
; Based on original MikhmonServer concept by Laksamadi Guko
; ============================================================

; Global variables
Global $sScriptDir = @ScriptDir
Global $sPhpDir = $sScriptDir & "\php"
Global $sPhpExe = $sPhpDir & "\php.exe"
Global $sMikhmonDir = $sScriptDir & "\mikhmon-ce"
Global $sPortFile = $sPhpDir & "\port.ini"
Global $iPort = 80
Global $iPID = 0
Global $sServerIP = ""
Global $bMinimizedToTray = False

; Read saved port
If FileExists($sPortFile) Then
    $iPort = FileReadLine($sPortFile, 1)
    If $iPort = "" Or Not IsInt(Int($iPort)) Then $iPort = 80
EndIf

; Get local IP
$sServerIP = _GetLocalIP()

; ============================================================
; Tray Setup
; ============================================================
Opt("TrayMenuMode", 3)
Opt("TrayOnEventMode", 1)

TraySetIcon(@ScriptDir & "\MikhMonCE_Server.exe", 1)
TraySetToolTip("MikhMon CE Server")

Local $trayShow = TrayCreateItem("Show")
TrayItemSetOnEvent($trayShow, "_ShowFromTray")
TrayCreateItem("")
Local $trayOpen = TrayCreateItem("Open MikhMon")
TrayItemSetOnEvent($trayOpen, "_OpenBrowser")
TrayCreateItem("")
Local $trayStop = TrayCreateItem("Stop Server")
TrayItemSetOnEvent($trayStop, "_StopFromTray")
TrayCreateItem("")
Local $trayExit = TrayCreateItem("Exit")
TrayItemSetOnEvent($trayExit, "_ExitApp")

; ============================================================
; GUI Setup
; ============================================================
Global $hGUI = GUICreate("MikhMon CE Server", 320, 240, -1, -1, $WS_CAPTION + $WS_SYSMENU + $WS_MINIMIZEBOX)

; Title label
GUICtrlCreateLabel("MikhMon CE Server", 0, 18, 320, 28, $SS_CENTER)
GUICtrlSetFont(-1, 14, 700)
GUICtrlSetColor(-1, 0x2d7fd4)

; Status / IP label
Global $lblStatus = GUICtrlCreateLabel("Starting server...", 0, 52, 320, 20, $SS_CENTER)
GUICtrlSetFont(-1, 9, 400)
GUICtrlSetColor(-1, 0x888888)

; Divider
GUICtrlCreateLabel("", 10, 78, 300, 1, $SS_ETCHEDHORZ)

; Buttons
Global $btnStart = GUICtrlCreateButton("Stop Server", 20, 92, 130, 34)
GUICtrlSetFont(-1, 9, 700)
GUICtrlSetBkColor(-1, 0x2d7fd4)
GUICtrlSetColor(-1, 0xFFFFFF)

Global $btnOpen = GUICtrlCreateButton("Open MikhMon", 165, 92, 130, 34)
GUICtrlSetFont(-1, 9, 400)

; Settings group
GUICtrlCreateGroup(" Settings ", 10, 140, 300, 58)
GUICtrlCreateLabel("Server Port:", 24, 162, 75, 20)
GUICtrlSetFont(-1, 9, 400)
Global $txtPort = GUICtrlCreateInput($iPort, 100, 159, 60, 22, $ES_NUMBER)
Global $btnChangePort = GUICtrlCreateButton("Change Port", 175, 158, 120, 24)
GUICtrlCreateGroup("", -99, -99, 1, 1)

; Footer
GUICtrlCreateLabel("MikhMon CE - Community Edition (GPL v2)", 0, 215, 320, 18, $SS_CENTER)
GUICtrlSetFont(-1, 7, 400)
GUICtrlSetColor(-1, 0x999999)

; Show GUI
GUISetState(@SW_SHOW, $hGUI)

; ============================================================
; Auto-start server on launch
; ============================================================
_StartServer()

; ============================================================
; Main Event Loop
; ============================================================
While 1
    Local $nMsg = GUIGetMsg()

    Switch $nMsg
        Case $GUI_EVENT_CLOSE
            ; Minimize to tray instead of closing
            GUISetState(@SW_HIDE, $hGUI)
            TraySetIcon(@ScriptDir & "\MikhMonCE_Server.exe", 1)
            TraySetToolTip("MikhMon CE Server - Running on port " & $iPort)
            $bMinimizedToTray = True

        Case $btnStart
            If $iPID = 0 Then
                _StartServer()
            Else
                _StopServer()
            EndIf

        Case $btnOpen
            If $iPID <> 0 Then
                ShellExecute("http://" & $sServerIP & ":" & $iPort)
            EndIf

        Case $btnChangePort
            Local $iNewPort = GUICtrlRead($txtPort)
            If $iNewPort >= 1 And $iNewPort <= 65535 Then
                If $iPID <> 0 Then
                    _StopServer()
                    $iPort = $iNewPort
                    FileDelete($sPortFile)
                    FileWriteLine($sPortFile, $iPort)
                    _StartServer()
                Else
                    $iPort = $iNewPort
                    FileDelete($sPortFile)
                    FileWriteLine($sPortFile, $iPort)
                    MsgBox($MB_ICONINFORMATION, "MikhMon CE", "Port changed to " & $iPort & ". Start the server to apply.")
                EndIf
            Else
                MsgBox($MB_ICONERROR, "MikhMon CE", "Invalid port number. Please enter a value between 1 and 65535.")
            EndIf
    EndSwitch

    ; Check if server process is still running
    If $iPID <> 0 Then
        If Not ProcessExists($iPID) Then
            $iPID = 0
            _SetStopped()
        EndIf
    EndIf
WEnd

; ============================================================
; Functions
; ============================================================

Func _StartServer()
    If Not FileExists($sPhpExe) Then
        MsgBox($MB_ICONERROR, "MikhMon CE", "PHP executable not found!" & @CRLF & $sPhpExe & @CRLF & @CRLF & "Make sure the php folder containing php.exe is in the same directory as this exe.")
        _SetStopped()
        Return
    EndIf

    If Not FileExists($sMikhmonDir & "\index.php") Then
        MsgBox($MB_ICONERROR, "MikhMon CE", "MikhMon CE files not found!" & @CRLF & $sMikhmonDir & @CRLF & @CRLF & "Make sure the mikhmon-ce folder is in the same directory as this exe.")
        _SetStopped()
        Return
    EndIf

    Local $sCmd = '"' & $sPhpExe & '" -S 0.0.0.0:' & $iPort & ' -t "' & $sMikhmonDir & '"'
    $iPID = Run($sCmd, $sScriptDir, @SW_HIDE)

    If $iPID = 0 Then
        MsgBox($MB_ICONERROR, "MikhMon CE", "Failed to start server!" & @CRLF & @CRLF & "Port " & $iPort & " may already be in use. Try changing the port.")
        _SetStopped()
        Return
    EndIf

    Sleep(1000)

    If Not ProcessExists($iPID) Then
        $iPID = 0
        MsgBox($MB_ICONERROR, "MikhMon CE", "Server failed to start!" & @CRLF & @CRLF & "Port " & $iPort & " may already be in use. Try changing the port.")
        _SetStopped()
        Return
    EndIf

    _SetRunning()
EndFunc

Func _StopServer()
    If $iPID <> 0 Then
        ProcessClose($iPID)
        $iPID = 0
    EndIf
    _SetStopped()
EndFunc

Func _SetRunning()
    GUICtrlSetData($lblStatus, "http://" & $sServerIP & ":" & $iPort)
    GUICtrlSetFont($lblStatus, 9, 700)
    GUICtrlSetColor($lblStatus, 0x2d7fd4)
    GUICtrlSetData($btnStart, "Stop Server")
    GUICtrlSetState($btnOpen, $GUI_ENABLE)
    TraySetToolTip("MikhMon CE Server - Running on port " & $iPort)
EndFunc

Func _SetStopped()
    GUICtrlSetData($lblStatus, "Server is stopped")
    GUICtrlSetFont($lblStatus, 9, 400)
    GUICtrlSetColor($lblStatus, 0x888888)
    GUICtrlSetData($btnStart, "Start Server")
    GUICtrlSetState($btnOpen, $GUI_DISABLE)
    TraySetToolTip("MikhMon CE Server - Stopped")
EndFunc

Func _ShowFromTray()
    GUISetState(@SW_SHOW, $hGUI)
    WinActivate($hGUI)
    $bMinimizedToTray = False
EndFunc

Func _OpenBrowser()
    If $iPID <> 0 Then
        ShellExecute("http://" & $sServerIP & ":" & $iPort)
    Else
        MsgBox($MB_ICONINFORMATION, "MikhMon CE", "Server is not running. Please open the launcher and start the server first.")
    EndIf
EndFunc

Func _StopFromTray()
    _StopServer()
    GUISetState(@SW_SHOW, $hGUI)
    $bMinimizedToTray = False
EndFunc

Func _ExitApp()
    _StopServer()
    Exit
EndFunc

Func _GetLocalIP()
    Local $sIP = ""
    Local $iPID2 = Run(@ComSpec & ' /c powershell -Command "(Get-NetIPAddress -AddressFamily IPv4 | Where-Object {$_.IPAddress -notlike ''127.*''} | Select-Object -First 1).IPAddress"', "", @SW_HIDE, 2)
    Local $iWait = 0
    While ProcessExists($iPID2) And $iWait < 30
        Sleep(100)
        $iWait += 1
    Wend
    Local $sOutput = StdoutRead($iPID2)
    $sIP = StringStripWS($sOutput, 3)
    If $sIP = "" Then $sIP = "localhost"
    Return $sIP
EndFunc
