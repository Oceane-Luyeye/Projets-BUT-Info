package fr.einfolearning.tp2.metiers;////////////////////////////////////////////////////////////////

import java.lang.StringBuffer;



/**
 * Classe TextBuffer
 * Buffer offrant des fonctionnalités d'insertion et de destruction de sous-chaines
 * @version 2023
 */
public class TextBuffer {

    private StringBuffer sb;

    public TextBuffer(String s) {
        sb = new StringBuffer(s);
    }


    // insert s from position in the buffer
    public void ins(String s, int position) {
        if ((position >= 0) && (position < sb.length()))
            sb.insert(position, s);
    }


    // delete a string between from to in the buffer
    public void del(int from, int to) {

        int max = maxP();
        if (from < 0)  from = 0;
        if (from > max) from=max;

        if (to < 0)  to = 0;
        if (to > max) to = max;

        sb.delete(from, to);
    }

    // Extract a substring between from to
    public String substr(int from, int to) {

        int max = maxP();
        if (from < 0)  from = 0;
        if (from > max) from=max;

        if (to < 0)  to = 0;
        if (to > max) to = max;

        return (sb.substring(from, to));
    }

    // Capacite maximal du buffer
    public int maxP() {
        return (sb.length());
    }

    public String toString() {
        return (sb.toString());
    }


}