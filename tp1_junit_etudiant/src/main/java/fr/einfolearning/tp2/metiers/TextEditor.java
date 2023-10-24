package fr.einfolearning.tp2.metiers;////////////////////////////////////////////////////////////////

import fr.einfolearning.tp2.metiers.exceptions.EmacsKillRingOverflowException;

import java.lang.IllegalAccessException;

/**
 * Classe TextEditor simulant un éditeur Emacs
 * @version 2023
 */
public class TextEditor {

    private TextBuffer buffer;     // text buffer
    private EmacsKillRing emacsKillring; // killring
    private int cursor, mark;   // cursor and mark position

    private boolean yankMode;   // true if yankpop can be called
    private int yankLeft, yankRight; // last section yanked

    ////////////////////////////////////////////////////////////////

    public TextEditor(String s) {
        buffer = new TextBuffer(s);
        emacsKillring = new EmacsKillRing();
        cursor = 0;
        mark = -1; // must be changed before manipulation
        yankMode = false;
        yankLeft = -1;
        yankRight = -1;
    }

    ////////////////////////////////////////////////////////////////


    /**
     * Mode yank
     * Permet de coller l'élément courant de la file circulaire à la position
     * courante du curseur
     *
     * Le mot inséré est marqué avec les curseurs yankLeft(yL) et yankRight(yR)
     *
     * Dans ce mode on peut faire  suivre un yank de plusieurs  yankPop
     *
     * @throws IllegalAccessException
     */
    public void yank() throws IllegalAccessException {
        String s;
        if (emacsKillring.isEmpty())
            throw (new IllegalAccessException(
                    "Cannot yank an empty kill ring"));

        yankMode = true;

        // retrieve the element
        s = emacsKillring.currentElt();
        // write into the buffer and store its position
        yankLeft = cursor;
        yankRight = cursor + s.length();
        buffer.ins(s, yankLeft);
    }


    /**
     * Permet de substituer le mot marqué (yank mode) dans le buffer de l'éditeur de texte par le prochain
     * élement qui se trouve dans la file circulaire
     *
     * Un yankPop doit-être précédé d'un yank c'est à dire qu'une zone doit-être marqué
     * par les curseurs yankLeft et yankRight
     *
     * @throws IllegalAccessException
     */
    public void yankPop() throws IllegalAccessException {
        String s;
        if (!yankMode) // throw exception if not in yank mode
            throw (new IllegalAccessException(
                    "Yankpop without yank not allowed"));
        if (emacsKillring.isEmpty()) // throw exception if empty killring
            throw (new IllegalAccessException(
                    "Cannot yank an empty kill ring"));
        emacsKillring.rotateFwd();
        s = emacsKillring.currentElt();
        buffer.del(yankLeft, yankRight);
        buffer.ins(s, yankLeft);
    }


    /**
     * Sauve une section de l'éditeur de texte marquée à l'aide du curseur
     * et de de la marque de fin de zone
     */
    public void killRingBackup() throws EmacsKillRingOverflowException {
        try {
            String s = buffer.substr(Math.min(cursor, mark),
                    Math.max(cursor, mark));
            emacsKillring.add(s);
        } catch (StringIndexOutOfBoundsException e) {
            System.out.println("Warning : region out of bounds");
        }
    }



    /**
     * Coupe et sauve une section de l'éditeur de texte marquée à l'aide du curseur
     * et de de la marque de fin de zone
     * @throws EmacsKillRingOverflowException
     */
    public void killSection() throws EmacsKillRingOverflowException {
        yankMode = false;
        killRingBackup();
        buffer.del(Math.min(cursor, mark),
                Math.max(cursor, mark));
        cursor = Math.min(cursor, mark);
        mark = cursor;
    }

    public String getBuffer() {
        String s = "\"" + buffer.toString() + "\"";
        s = s + emacsKillring.toString() + "\"";
        return (s);
    }

    public TextBuffer getTextBuffer(){
        return this.buffer;
    }
    public void setCursor(int pos) {
        yankMode = false;
        if ((pos < 0) || (pos >= buffer.maxP()))
            cursor = pos % (buffer.maxP());
        else cursor = pos;
    }

    public void setMark(int pos) {
        yankMode = false;
        if ((pos < 0) || (pos >= buffer.maxP()))
            mark = pos % (buffer.maxP());
        else mark = pos;
    }

    public int getCursor() {
        return cursor;
    }

    public int getMark() {
        return mark;
    }

    public boolean isYankMode() {
        return yankMode;
    }

    public int getYankLeft() {
        return yankLeft;
    }

    public int getYankRight() {
        return yankRight;
    }
}

