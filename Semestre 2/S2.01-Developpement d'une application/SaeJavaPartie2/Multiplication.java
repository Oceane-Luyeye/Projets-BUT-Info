public class Multiplication extends Operation {
    
    //Constructeur champ à champ
    public Multiplication(Expression operande1, Expression operande2){
        super(operande1,operande2);
    }
    
    //retourne un int representant le resultat de loperation
    public int valeur() {
        return this.getOperande1().valeur() * this.getOperande2().valeur();
    }
    
    // retourne un String representant loperation
    public String toString() {
        return "[" + this.getOperande1() + "*" + this.getOperande2() + "]";
    }   

}
